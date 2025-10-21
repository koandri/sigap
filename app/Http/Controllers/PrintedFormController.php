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
