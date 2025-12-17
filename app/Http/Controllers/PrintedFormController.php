<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PrintedForm;
use App\Services\DocumentAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

final class PrintedFormController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $accessService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
        
        // Get filters from request (support arrays for multi-select)
        $filters = [
            'status' => $request->input('status', []),
            'issued_to' => $request->input('issued_to', []),
            'form_number' => $request->input('form_number'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
        
        // Normalize filters (handle both single values and arrays)
        if (!is_array($filters['status'])) {
            $filters['status'] = $filters['status'] ? [$filters['status']] : [];
        }
        if (!is_array($filters['issued_to'])) {
            $filters['issued_to'] = $filters['issued_to'] ? [$filters['issued_to']] : [];
        }
        
        // Build query
        $query = PrintedForm::with(['formRequestItem.formRequest.requester', 'documentVersion.document', 'issuedTo']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }
        
        if (!empty($filters['issued_to'])) {
            $query->whereIn('issued_to', $filters['issued_to']);
        }
        
        if ($filters['form_number']) {
            $query->where('form_number', 'like', '%' . $filters['form_number'] . '%');
        }
        
        if ($filters['date_from']) {
            $query->whereDate('issued_at', '>=', $filters['date_from']);
        }
        
        if ($filters['date_to']) {
            $query->whereDate('issued_at', '<=', $filters['date_to']);
        }
        
        // If not admin, only show forms the user can return (issued to them or to their staff)
        if (!$isAdmin) {
            $staffIds = $user->staff()->pluck('id')->toArray();
            $query->where(function($q) use ($user, $staffIds) {
                $q->where('issued_to', $user->id);
                if (!empty($staffIds)) {
                    $q->orWhereIn('issued_to', $staffIds);
                }
            });
        }
        
        $printedForms = $query->latest('issued_at')->paginate(20);
        
        // Get users for filter dropdown
        $users = $isAdmin ? \App\Models\User::orderBy('name')->get() : collect();
        
        return view('printed-forms.index', compact('printedForms', 'filters', 'users', 'isAdmin'));
    }

    public function show(PrintedForm $printedForm): View
    {
        $printedForm->load(['formRequestItem.formRequest.requester', 'documentVersion.document', 'issuedTo']);
        
        return view('printed-forms.show', compact('printedForm'));
    }

    public function requestAccess(PrintedForm $printedForm): RedirectResponse
    {
        $user = auth()->user();
        
        if (!$this->accessService->checkAccess($user, $printedForm->documentVersion)) {
            abort(403, 'You do not have access to this document version.');
        }

        return redirect()->route('document-versions.view', $printedForm->documentVersion);
    }

    public function viewScanned(PrintedForm $printedForm): Response
    {
        if (!$printedForm->scanned_file_path) {
            abort(404, 'Scanned file not found.');
        }

        if (!Storage::disk('s3')->exists($printedForm->scanned_file_path)) {
            abort(404, 'Scanned file not found.');
        }

        $content = Storage::disk('s3')->get($printedForm->scanned_file_path);
        
        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $printedForm->form_name . '_scanned.pdf"');
    }

    public function returnForm(Request $request, PrintedForm $printedForm): RedirectResponse
    {
        $this->authorize('returnForm', $printedForm);

        // Only allow returning forms that are in "Circulating" status
        if ($printedForm->status->value !== 'circulating') {
            return redirect()->route('printed-forms.show', $printedForm)
                ->with('error', 'Only forms in "Circulating" status can be returned. Current status: ' . $printedForm->status->label());
        }

        $status = $request->input('status');
        
        $rules = [
            'status' => 'required|in:returned,lost,spoilt',
            'notes' => 'nullable|string|max:1000',
        ];
        
        // Require notes for Lost or Spoilt status
        if (in_array($status, ['lost', 'spoilt'])) {
            $rules['notes'] = 'required|string|max:1000';
        }
        
        $request->validate($rules);

        $printedForm->update([
            'status' => $status,
            'returned_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        $statusLabels = [
            'returned' => 'returned',
            'lost' => 'lost',
            'spoilt' => 'spoilt',
        ];

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Form marked as ' . $statusLabels[$status] . ' successfully.');
    }

    public function receive(PrintedForm $printedForm): RedirectResponse
    {
        $this->authorize('process', $printedForm->formRequestItem->formRequest);
        
        $printedForm->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Form marked as received.');
    }

    public function uploadScans(Request $request, PrintedForm $printedForm): RedirectResponse
    {
        $this->authorize('process', $printedForm->formRequestItem->formRequest);
        
        $request->validate([
            'scanned_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $filePath = $request->file('scanned_file')->store('documents/scanned', 's3');
        
        $printedForm->update([
            'status' => 'scanned',
            'scanned_file_path' => $filePath,
            'scanned_at' => now(),
        ]);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Scanned form uploaded successfully.');
    }

    public function updatePhysicalLocation(Request $request, PrintedForm $printedForm): RedirectResponse
    {
        $this->authorize('process', $printedForm->formRequestItem->formRequest);
        
        $request->validate([
            'physical_location' => 'nullable|array',
            'physical_location.room_no' => 'nullable|string|max:255',
            'physical_location.cabinet_no' => 'nullable|string|max:255',
            'physical_location.shelf_no' => 'nullable|string|max:255',
        ]);

        $printedForm->update([
            'physical_location' => $request->input('physical_location') ?: null,
        ]);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Physical location updated successfully.');
    }

    public function bulkReturn(Request $request): RedirectResponse
    {
        $status = $request->input('status');
        
        $rules = [
            'form_ids' => 'required|array|min:1',
            'form_ids.*' => 'required|exists:printed_forms,id',
            'status' => 'required|in:returned,lost,spoilt',
            'notes' => 'nullable|string|max:1000',
        ];
        
        // Require notes for Lost or Spoilt status
        if (in_array($status, ['lost', 'spoilt'])) {
            $rules['notes'] = 'required|string|max:1000';
        }
        
        $request->validate($rules);

        $formIds = $request->input('form_ids');
        $printedForms = PrintedForm::with(['formRequestItem.formRequest'])
            ->whereIn('id', $formIds)
            ->get();

        // Check authorization and validate status for each form
        $errors = [];
        $authorizedForms = [];
        
        foreach ($printedForms as $printedForm) {
            try {
                $this->authorize('returnForm', $printedForm);
                
                if ($printedForm->status->value !== 'circulating') {
                    $errors[] = "Form {$printedForm->form_number} cannot be updated. Current status: {$printedForm->status->label()}";
                    continue;
                }
                
                $authorizedForms[] = $printedForm;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $errors[] = "You do not have permission to update form {$printedForm->form_number}";
            }
        }

        if (empty($authorizedForms)) {
            return redirect()->route('printed-forms.index')
                ->with('error', 'No forms were updated. ' . implode(' ', $errors));
        }

        // Update all authorized forms
        $updated = PrintedForm::whereIn('id', collect($authorizedForms)->pluck('id'))
            ->where('status', 'circulating')
            ->update([
                'status' => $status,
                'returned_at' => now(),
                'notes' => $request->input('notes'),
            ]);

        $statusLabels = [
            'returned' => 'returned',
            'lost' => 'lost',
            'spoilt' => 'spoilt',
        ];

        $successMessage = "Successfully marked {$updated} form(s) as {$statusLabels[$status]}.";
        if (!empty($errors)) {
            $successMessage .= ' ' . implode(' ', $errors);
        }

        return redirect()->route('printed-forms.index')
            ->with('success', $successMessage);
    }

    public function bulkReceive(Request $request): RedirectResponse
    {
        $rules = [
            'form_ids' => 'required|array|min:1',
            'form_ids.*' => 'required|exists:printed_forms,id',
        ];
        
        $request->validate($rules);

        $formIds = $request->input('form_ids');
        $printedForms = PrintedForm::with(['formRequestItem.formRequest'])
            ->whereIn('id', $formIds)
            ->get();

        // Check authorization and validate status for each form
        $errors = [];
        $authorizedForms = [];
        
        foreach ($printedForms as $printedForm) {
            try {
                $this->authorize('process', $printedForm->formRequestItem->formRequest);
                
                // Only allow receiving forms that are returned (not lost/spoilt)
                if (!$printedForm->isReturned()) {
                    $errors[] = "Form {$printedForm->form_number} is not in a returnable status. Current status: {$printedForm->status->label()}";
                    continue;
                }
                
                if ($printedForm->isProblematic()) {
                    $errors[] = "Form {$printedForm->form_number} cannot be received (Lost/Spoilt status)";
                    continue;
                }
                
                if ($printedForm->isReceived()) {
                    $errors[] = "Form {$printedForm->form_number} is already received";
                    continue;
                }
                
                $authorizedForms[] = $printedForm;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $errors[] = "You do not have permission to receive form {$printedForm->form_number}";
            }
        }

        if (empty($authorizedForms)) {
            return redirect()->route('printed-forms.index')
                ->with('error', 'No forms were received. ' . implode(' ', $errors));
        }

        // Update all authorized forms (only returned status, not lost/spoilt)
        $updated = PrintedForm::whereIn('id', collect($authorizedForms)->pluck('id'))
            ->where('status', 'returned')
            ->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

        $successMessage = "Successfully marked {$updated} form(s) as received.";
        if (!empty($errors)) {
            $successMessage .= ' Some forms were skipped: ' . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $successMessage .= ' and ' . (count($errors) - 5) . ' more.';
            }
        }

        return redirect()->route('printed-forms.index')
            ->with('success', $successMessage);
    }

    public function bulkUploadScans(Request $request): RedirectResponse
    {
        $rules = [
            'form_ids' => 'required|array|min:1',
            'form_ids.*' => 'required|exists:printed_forms,id',
            'scanned_files' => 'required|array|min:1',
        ];
        
        $request->validate($rules);

        $formIds = $request->input('form_ids');
        $printedForms = PrintedForm::with(['formRequestItem.formRequest'])
            ->whereIn('id', $formIds)
            ->get();

        // Build dynamic validation rules for files mapped by form ID
        $fileRules = [];
        foreach ($formIds as $formId) {
            $fileRules["scanned_files.{$formId}"] = 'required|file|mimes:pdf|max:10240';
        }
        $request->validate($fileRules);

        // Check authorization and validate status for each form
        $errors = [];
        $authorizedForms = [];
        
        foreach ($printedForms as $printedForm) {
            try {
                $this->authorize('process', $printedForm->formRequestItem->formRequest);
                
                if (!$printedForm->isReceived()) {
                    $errors[] = "Form {$printedForm->form_number} must be received before scanning. Current status: {$printedForm->status->label()}";
                    continue;
                }
                
                if ($printedForm->scanned_file_path) {
                    $errors[] = "Form {$printedForm->form_number} already has a scanned file";
                    continue;
                }
                
                // Check if file was uploaded for this form
                if (!$request->hasFile("scanned_files.{$printedForm->id}")) {
                    $errors[] = "No file uploaded for form {$printedForm->form_number}";
                    continue;
                }
                
                $authorizedForms[] = $printedForm;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $errors[] = "You do not have permission to scan form {$printedForm->form_number}";
            }
        }

        if (empty($authorizedForms)) {
            return redirect()->route('printed-forms.index')
                ->with('error', 'No forms were scanned. ' . implode(' ', $errors));
        }

        $updated = 0;
        $fileErrors = [];

        // Process each form with its specific file
        foreach ($authorizedForms as $printedForm) {
            $file = $request->file("scanned_files.{$printedForm->id}");
            
            if (!$file) {
                $fileErrors[] = "No file found for form {$printedForm->form_number}";
                continue;
            }
            
            try {
                $filePath = $file->store('documents/scanned', 's3');
                
                $printedForm->update([
                    'status' => 'scanned',
                    'scanned_file_path' => $filePath,
                    'scanned_at' => now(),
                ]);
                
                $updated++;
            } catch (\Exception $e) {
                $fileErrors[] = "Failed to upload file for form {$printedForm->form_number}: " . $e->getMessage();
            }
        }

        $successMessage = "Successfully uploaded scanned files for {$updated} form(s).";
        if (!empty($errors)) {
            $successMessage .= ' Some forms were skipped: ' . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $successMessage .= ' and ' . (count($errors) - 3) . ' more.';
            }
        }
        if (!empty($fileErrors)) {
            $successMessage .= ' File errors: ' . implode(', ', array_slice($fileErrors, 0, 3));
            if (count($fileErrors) > 3) {
                $successMessage .= ' and ' . (count($fileErrors) - 3) . ' more.';
            }
        }

        return redirect()->route('printed-forms.index')
            ->with('success', $successMessage);
    }

    public function bulkUpdatePhysicalLocation(Request $request): RedirectResponse
    {
        $rules = [
            'form_ids' => 'required|array|min:1',
            'form_ids.*' => 'required|exists:printed_forms,id',
            'physical_locations' => 'required|array',
        ];
        
        $request->validate($rules);

        $formIds = $request->input('form_ids');
        $printedForms = PrintedForm::with(['formRequestItem.formRequest'])
            ->whereIn('id', $formIds)
            ->get()
            ->keyBy('id'); // Key by ID for easier lookup

        // Check authorization and validate status for each form
        $errors = [];
        $authorizedForms = [];
        
        foreach ($formIds as $formId) {
            if (!isset($printedForms[$formId])) {
                $errors[] = "Form ID {$formId} not found";
                continue;
            }
            
            $printedForm = $printedForms[$formId];
            
            try {
                $this->authorize('process', $printedForm->formRequestItem->formRequest);
                
                if (!$printedForm->scanned_file_path) {
                    $errors[] = "Form {$printedForm->form_number} must be scanned before updating location";
                    continue;
                }
                
                $authorizedForms[$formId] = $printedForm;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $errors[] = "You do not have permission to update location for form {$printedForm->form_number}";
            }
        }

        if (empty($authorizedForms)) {
            return redirect()->route('printed-forms.index')
                ->with('error', 'No forms were updated. ' . implode(' ', $errors));
        }

        $updated = 0;
        $updateErrors = [];
        $allPhysicalLocations = $request->input('physical_locations', []);

        // Update each form with its specific location
        foreach ($authorizedForms as $formId => $printedForm) {
            // Get location data for this specific form
            $location = $allPhysicalLocations[$formId] ?? null;
            
            // Process location data - handle both array and null cases
            $locationData = null;
            
            if (is_array($location)) {
                // Trim whitespace from each field
                $roomNo = isset($location['room_no']) ? trim((string)$location['room_no']) : '';
                $cabinetNo = isset($location['cabinet_no']) ? trim((string)$location['cabinet_no']) : '';
                $shelfNo = isset($location['shelf_no']) ? trim((string)$location['shelf_no']) : '';
                
                // Only set location if at least one field has a non-empty value
                if ($roomNo !== '' || $cabinetNo !== '' || $shelfNo !== '') {
                    $locationData = [
                        'room_no' => $roomNo !== '' ? $roomNo : null,
                        'cabinet_no' => $cabinetNo !== '' ? $cabinetNo : null,
                        'shelf_no' => $shelfNo !== '' ? $shelfNo : null,
                    ];
                }
            }
            
            try {
                // Refresh the model to ensure we have the latest data
                $printedForm->refresh();
                
                $printedForm->update([
                    'physical_location' => $locationData,
                ]);
                
                // Verify the update
                $printedForm->refresh();
                if ($locationData === null && $printedForm->physical_location !== null) {
                    throw new \Exception('Location was not cleared properly');
                } elseif ($locationData !== null && json_encode($printedForm->physical_location) !== json_encode($locationData)) {
                    throw new \Exception('Location was not saved correctly');
                }
                
                $updated++;
            } catch (\Exception $e) {
                $updateErrors[] = "Failed to update location for form {$printedForm->form_number} (ID: {$formId}): " . $e->getMessage();
            }
        }

        $successMessage = "Successfully updated physical location for {$updated} form(s).";
        if (!empty($errors)) {
            $successMessage .= ' Some forms were skipped: ' . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $successMessage .= ' and ' . (count($errors) - 5) . ' more.';
            }
        }
        if (!empty($updateErrors)) {
            $successMessage .= ' Update errors: ' . implode(', ', array_slice($updateErrors, 0, 3));
            if (count($updateErrors) > 3) {
                $successMessage .= ' and ' . (count($updateErrors) - 3) . ' more.';
            }
        }

        return redirect()->route('printed-forms.index')
            ->with('success', $successMessage);
    }
}
