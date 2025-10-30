<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AccessType;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\DocumentVersion;
use App\Services\DocumentAccessService;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class DocumentAccessController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $accessService,
        private readonly WatermarkService $watermarkService
    ) {}

    public function myAccess(): View
    {
        $user = Auth::user();
        $accessibleDocuments = $this->accessService->getUserAccessibleDocuments($user);
        
        return view('document-access.my-access', compact('accessibleDocuments'));
    }

    public function requestAccess(Document $document): View|RedirectResponse
    {
        $this->authorize('view', $document);
        
        // Super Admin and Owner don't need to request access
        if (Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            return redirect()->route('documents.show', $document)
                ->with('info', 'You have full access to all documents as an administrator.');
        }
        
        $activeVersion = $document->activeVersion;
        if (!$activeVersion) {
            return redirect()->route('documents.show', $document)
                ->with('warning', 'Access requests are only available for documents with an active version. This document does not have an active version yet.');
        }

        $accessTypes = AccessType::cases();
        
        return view('document-access.request-form', compact('document', 'activeVersion', 'accessTypes'));
    }

    public function storeAccessRequest(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('view', $document);
        
        // Super Admin and Owner don't need to request access
        if (Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            return redirect()->route('documents.show', $document)
                ->with('info', 'You have full access to all documents as an administrator.');
        }
        
        $request->validate([
            'access_type' => 'required|string',
            'requested_expiry_date' => 'nullable|date|after:now',
            'reason' => 'required|string|max:1000',
        ]);

        $activeVersion = $document->activeVersion;
        if (!$activeVersion) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Access requests are only available for documents with an active version. This document does not have an active version yet.');
        }

        $this->accessService->createAccessRequest($activeVersion, Auth::user(), $request->all());

        return redirect()->route('my-document-access')
            ->with('success', 'Access request submitted successfully.');
    }

    public function pendingRequests(): View
    {
        $this->authorize('approve', DocumentAccessRequest::class);
        
        $pendingRequests = $this->accessService->getPendingRequestsForApprover(Auth::user());
        
        return view('document-access.pending', compact('pendingRequests'));
    }

    public function approve(Request $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
        $this->authorize('approve', $accessRequest);
        
        $request->validate([
            'approved_access_type' => 'required|string',
            'approved_expiry_date' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $modifications = [
            'access_type' => $request->approved_access_type,
            'expiry_date' => $request->approved_expiry_date,
        ];

        $this->accessService->approveAccessRequest($accessRequest, Auth::user(), $modifications);

        return redirect()->route('document-access-requests.pending')
            ->with('success', 'Access request approved successfully.');
    }

    public function reject(Request $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
        $this->authorize('approve', $accessRequest);
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->accessService->rejectAccessRequest($accessRequest, Auth::user(), $request->reason);

        return redirect()->route('document-access-requests.pending')
            ->with('success', 'Access request rejected successfully.');
    }

    public function viewDocument(DocumentVersion $version): Response
    {
        $user = Auth::user();
        
        if (!$this->accessService->checkAccess($user, $version)) {
            abort(403, 'You do not have access to this document version.');
        }

        // Log the access
        $this->accessService->logAccess($user, $version, request()->ip());

        // Apply watermark if needed
        $filePath = $version->file_path;
        
        if (!Storage::disk('s3')->exists($filePath)) {
            abort(404, 'Document file not found.');
        }

        // For documents that require watermarking
        if ($version->document->document_type->requiresAccessRequest()) {
            $watermarkedPath = $this->watermarkService->applyWatermarkToPdf($filePath, $user);
            $content = Storage::disk('s3')->get($watermarkedPath);
        } else {
            $content = Storage::disk('s3')->get($filePath);
        }

        $mimeType = $this->getMimeType($version->file_type);
        
        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $version->document->title . '.' . $version->file_type . '"');
    }

    private function getMimeType(string $fileType): string
    {
        return match ($fileType) {
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }
}
