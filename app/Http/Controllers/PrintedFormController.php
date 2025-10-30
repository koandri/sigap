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
        
        // Get filters from request
        $filters = [
            'status' => $request->input('status'),
            'issued_to' => $request->input('issued_to'),
            'form_number' => $request->input('form_number'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
        
        // Build query
        $query = PrintedForm::with(['formRequestItem.formRequest.requester', 'documentVersion.document', 'issuedTo']);
        
        // Apply filters
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        if ($filters['issued_to']) {
            $query->where('issued_to', $filters['issued_to']);
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
        
        // If not admin, only show forms issued to the user
        if (!$isAdmin) {
            $query->where('issued_to', $user->id);
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

    public function track(PrintedForm $printedForm): View
    {
        $printedForm->load(['formRequestItem.formRequest.requester', 'documentVersion.document', 'issuedTo']);
        
        return view('printed-forms.track', compact('printedForm'));
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
        $request->validate([
            'status' => 'required|in:returned,lost,spoilt',
            'notes' => 'nullable|string|max:1000',
        ]);

        $printedForm->update([
            'status' => $request->status,
            'returned_at' => now(),
        ]);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Form return status updated successfully.');
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
}
