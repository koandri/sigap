<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
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
            'search' => $request->input('search'),
        ];
        
        if ($isAdmin) {
            $formRequests = $this->formRequestService->getFilteredFormRequests($filters);
            $requesters = $this->formRequestService->getAllRequesters();
        } else {
            $formRequests = $this->formRequestService->getFilteredFormRequestsByUser($user, $filters);
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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'forms' => 'required|array|min:1',
            'forms.*.document_version_id' => 'required|exists:document_versions,id',
            'forms.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        $this->formRequestService->createFormRequest(Auth::user(), $request->all());

        return redirect()->route('form-requests.index')
            ->with('success', 'Form request submitted successfully.');
    }

    public function show(FormRequest $formRequest): View
    {
        $this->authorize('view', $formRequest);
        
        $formRequest->load(['items.documentVersion.document', 'requester', 'acknowledger']);
        
        return view('form-requests.show', compact('formRequest'));
    }

    public function edit(FormRequest $formRequest): View
    {
        $this->authorize('update', $formRequest);
        
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

    public function printLabels(FormRequest $formRequest): StreamedResponse
    {
        $this->authorize('process', $formRequest);
        
        $labelPath = $this->qrCodeService->generateFormLabel($formRequest);
        
        return Storage::disk('s3')->download($labelPath);
    }

    public function collect(FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('view', $formRequest);
        
        $this->formRequestService->markCollected($formRequest);

        return redirect()->route('form-requests.show', $formRequest)
            ->with('success', 'Form request marked as collected.');
    }

    public function returnForm(Request $request, PrintedForm $printedForm): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:returned,lost,spoilt',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->formRequestService->markReturned($printedForm, $request->status, $request->notes);

        return redirect()->route('printed-forms.show', $printedForm)
            ->with('success', 'Form return status updated successfully.');
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
