<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVersionRequest;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\DocumentVersionService;
use App\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

final class DocumentVersionController extends Controller
{
    public function __construct(
        private readonly DocumentVersionService $versionService,
        private readonly OnlyOfficeService $onlyOfficeService
    ) {}

    public function create(Document $document): View
    {
        $this->authorize('create', [DocumentVersion::class, $document]);
        
        $versions = $document->versions()->orderBy('version_number', 'desc')->get();
        
        return view('document-versions.create', compact('document', 'versions'));
    }

    public function store(StoreVersionRequest $request, Document $document): RedirectResponse
    {
        $version = match ($request->creation_method) {
            'scratch' => $this->versionService->createVersionFromScratch($document, $request->file_type),
            'upload' => $this->handleFileUpload($document, $request),
            'copy' => $this->versionService->createVersionFromCopy($document, DocumentVersion::find($request->source_version_id)),
        };

        if ($request->revision_description) {
            $version->update(['revision_description' => $request->revision_description]);
        }
        
        // Update NCR paper setting for forms
        if ($document->document_type->value === 'form') {
            $version->update(['is_ncr_paper' => $request->boolean('is_ncr_paper')]);
        }

        return redirect()->route('document-versions.editor', $version)
            ->with('success', 'Document version created successfully.');
    }


    public function edit(DocumentVersion $version): View|RedirectResponse
    {
        $this->authorize('edit', $version);
        
        // If file_path is empty (created from scratch), create the initial empty document
        if (empty($version->file_path)) {
            try {
                $this->onlyOfficeService->createDocument($version, $version->file_type);
                // Refresh the version to get the updated file_path
                $version->refresh();
            } catch (\Exception $e) {
                return redirect()
                    ->route('documents.show', $version->document)
                    ->with('error', 'Unable to create initial document. Error: ' . $e->getMessage());
            }
        } else {
            // Check if the file exists in storage
            try {
                if (!Storage::disk('s3')->exists($version->file_path)) {
                    return redirect()
                        ->route('documents.show', $version->document)
                        ->with('error', 'The document file does not exist in storage. The file may have been deleted or moved.');
                }
            } catch (\Exception $e) {
                return redirect()
                    ->route('documents.show', $version->document)
                    ->with('error', 'Unable to access storage. Please check your S3 configuration. Error: ' . $e->getMessage());
            }
        }
        
        $editorConfig = $this->onlyOfficeService->getEditorConfig($version);
        
        return view('document-versions.editor', compact('version', 'editorConfig'));
    }

    public function update(Request $request, DocumentVersion $version): JsonResponse
    {
        $this->authorize('edit', $version);
        
        // This handles OnlyOffice callback
        $this->onlyOfficeService->handleCallback($version, $request->all());
        
        return response()->json(['error' => 0]);
    }
    
    public function onlyofficeCallback(Request $request, DocumentVersion $version): JsonResponse
    {
        // OnlyOffice callback - no auth required (secured by JWT in payload)
        // Log the callback for debugging
        \Log::info('OnlyOffice Callback Received', [
            'version_id' => $version->id,
            'payload' => $request->all(),
        ]);
        
        try {
            $this->onlyOfficeService->handleCallback($version, $request->all());
            return response()->json(['error' => 0]);
        } catch (\Exception $e) {
            \Log::error('OnlyOffice Callback Error', [
                'version_id' => $version->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 1, 'message' => $e->getMessage()], 500);
        }
    }

    public function submitForApproval(DocumentVersion $version): RedirectResponse
    {
        $this->authorize('edit', $version);
        
        try {
            $this->versionService->submitForApproval($version);
            
            return redirect()->route('documents.show', $version->document)
                ->with('success', 'Version submitted for approval.');
        } catch (\Exception $e) {
            \Log::error('Document version submission failed', [
                'version_id' => $version->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to submit for approval: ' . $e->getMessage());
        }
    }

    public function viewPDF(DocumentVersion $version): RedirectResponse
    {
        $this->authorize('view', $version);
        
        // Check if user has access to this version
        if (!$this->versionService->checkAccess(auth()->user(), $version)) {
            abort(403, 'You do not have access to this document version.');
        }

        // Check if the file exists in storage
        try {
            if (!Storage::disk('s3')->exists($version->file_path)) {
                return redirect()
                    ->route('documents.show', $version->document)
                    ->with('error', 'The document file does not exist in storage. The file may have been deleted or moved.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('documents.show', $version->document)
                ->with('error', 'Unable to access storage. Please check your S3 configuration. Error: ' . $e->getMessage());
        }

        // Redirect to OnlyOffice editor for viewing
        return redirect()->route('document-versions.editor', $version);
    }

    private function handleFileUpload(Document $document, Request $request): DocumentVersion
    {
        $file = $request->file('source_file');
        $fileType = $file->getClientOriginalExtension();
        $filePath = $file->store('documents/versions/' . $document->id, 's3');
        
        return $this->versionService->createVersionFromUpload($document, $filePath, $fileType);
    }


}
