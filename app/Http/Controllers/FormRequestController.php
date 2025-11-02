<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\StoreFormRequestRequest;
use App\Models\Document;
use App\Models\FormRequest;
use App\Models\PrintedForm;
use App\Services\FormRequestService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FormRequestController extends Controller
{
    public function __construct(
        private readonly FormRequestService $formRequestService,
        private readonly QRCodeService $qrCodeService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
        
        // Get filters from request
        $filters = [
            'status' => $request->input('status'),
            'requester' => $request->input('requester'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
        
        if ($isAdmin) {
            $query = \App\Models\FormRequest::with(['items.documentVersion.document', 'requester']);
            $this->formRequestService->applyFiltersToQuery($query, $filters);
            $formRequests = $query->orderBy('request_date', 'desc')->paginate(20);
            $requesters = $this->formRequestService->getAllRequesters();
        } else {
            $query = \App\Models\FormRequest::with(['items.documentVersion.document', 'requester'])
                ->where('requested_by', $user->id);
            $this->formRequestService->applyFiltersToQuery($query, $filters);
            $formRequests = $query->orderBy('request_date', 'desc')->paginate(20);
            $requesters = collect();
        }
        
        return view('form-requests.index', compact('formRequests', 'filters', 'requesters', 'isAdmin'));
    }

    public function create(): View
    {
        $formDocuments = Document::where('document_type', DocumentType::Form)
            ->whereHas('activeVersion')
            ->with(['activeVersion', 'department'])
            ->get();
        
        return view('form-requests.create', compact('formDocuments'));
    }

    public function store(StoreFormRequestRequest $request): RedirectResponse
    {
        $this->formRequestService->createFormRequest(Auth::user(), $request->all());

        return redirect()->route('form-requests.index')
            ->with('success', 'Form request submitted successfully.');
    }

    public function show(FormRequest $formRequest): View
    {
        $this->authorize('view', $formRequest);
        
        $formRequest->load([
            'items.documentVersion.document',
            'items.printedForms.issuedTo',
            'requester',
            'acknowledger'
        ]);
        
        return view('form-requests.show', compact('formRequest'));
    }

    public function edit(FormRequest $formRequest): View|RedirectResponse
    {
        $this->authorize('update', $formRequest);
        
        // Additional check: Only allow editing when status is Requested (not Acknowledged or later)
        if (!$formRequest->isPending()) {
            return redirect()->route('form-requests.show', $formRequest)
                ->with('error', 'Form requests can only be edited when the status is "Requested". Once acknowledged or later, the request cannot be modified.');
        }
        
        $formRequest->load(['items.documentVersion.document.department']);
        
        $formDocuments = Document::where('document_type', DocumentType::Form)
            ->whereHas('activeVersion')
            ->with(['activeVersion', 'department'])
            ->get();
        
        return view('form-requests.edit', compact('formRequest', 'formDocuments'));
    }

    public function update(Request $request, FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('update', $formRequest);
        
        // Additional check: Only allow updating when status is Requested (not Acknowledged or later)
        if (!$formRequest->isPending()) {
            return redirect()->route('form-requests.show', $formRequest)
                ->with('error', 'Form requests can only be updated when the status is "Requested". Once acknowledged or later, the request cannot be modified.');
        }
        
        $request->validate([
            'forms' => 'required|array|min:1',
            'forms.*.document_version_id' => 'required|exists:document_versions,id',
            'forms.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        $this->formRequestService->updateFormRequest($formRequest, $request->all());

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request updated successfully.');
    }

    public function acknowledge(FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('process', $formRequest);
        
        $this->formRequestService->acknowledgeRequest($formRequest, Auth::user());

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request acknowledged successfully.');
    }

    public function process(FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('process', $formRequest);
        
        $this->formRequestService->processRequest($formRequest);

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request processing started.');
    }

    public function markReady(FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('process', $formRequest);
        
        $this->formRequestService->markReady($formRequest);

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request marked as ready for collection.');
    }

    public function printLabels(FormRequest $formRequest): View|RedirectResponse
    {
        $this->authorize('process', $formRequest);
        
        // Get printed forms with relationships loaded to check if forms have been issued
        $printedForms = $formRequest->items()
            ->with(['printedForms.documentVersion.document'])
            ->get()
            ->flatMap(function ($item) {
                return $item->printedForms;
            });

        // Check if there are any printed forms
        if ($printedForms->isEmpty()) {
            return redirect()->route('form-requests.show', $formRequest)
                ->with('error', 'Cannot print labels: No printed forms found for this request. Please process the form request first.');
        }

        // Check if forms have been issued (have issued_at timestamp)
        $formsIssued = $printedForms->contains(function ($printedForm) {
            return $printedForm->issued_at !== null;
        });

        // Only allow printing labels when:
        // 1. Status is Processing (Processing Started), OR
        // 2. Forms have been issued (Forms Issued)
        // But NOT when status is Ready or later (labels should have already been printed)
        $canPrintLabels = ($formRequest->isProcessing() || $formsIssued) 
            && !$formRequest->isReady() 
            && !$formRequest->isCollected() 
            && !$formRequest->isCompleted();
        
        if (!$canPrintLabels) {
            if ($formRequest->isReady() || $formRequest->isCollected() || $formRequest->isCompleted()) {
                return redirect()->route('form-requests.show', $formRequest)
                    ->with('error', 'Labels can only be printed when processing has started or forms have been issued. Once forms are ready for collection or later, labels should have already been printed and applied to forms.');
            }
            
            return redirect()->route('form-requests.show', $formRequest)
                ->with('error', 'Labels can only be printed when processing has started or forms have been issued. Please process the form request first.');
        }

        // Generate label data
        $labels = $printedForms->flatMap(function ($printedForm) {
            $labelData = $this->qrCodeService->generateSingleLabel($printedForm);
            
            // If NCR paper, generate 3 copies of the label
            if ($printedForm->documentVersion->is_ncr_paper) {
                return collect()->times(3, fn() => $labelData);
            }
            
            return [$labelData];
        });
        
        // Return HTML view directly for browser printing
        return view('form-requests.labels', [
            'labels' => $labels,
            'request' => $formRequest,
        ]);
    }

    public function collect(FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('view', $formRequest);
        
        $this->formRequestService->markCollected($formRequest);

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request marked as collected.');
    }


    public function receive(PrintedForm $printedForm): RedirectResponse
    {
        $this->authorize('process', $printedForm->formRequestItem->formRequest);
        
        $this->formRequestService->markReceived($printedForm);

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
        
        $this->formRequestService->uploadScannedForm($printedForm, $filePath);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Scanned form uploaded successfully.');
    }
}
