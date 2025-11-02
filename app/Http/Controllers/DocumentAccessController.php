<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AccessType;
use App\Enums\DocumentType;
use App\Helpers\MimeTypeHelper;
use App\Http\Requests\ApproveAccessRequest;
use App\Http\Requests\RequestAccessRequest;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\DocumentVersion;
use App\Models\Department;
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

    public function myAccess(Request $request): View
    {
        $user = Auth::user();
        $accessibleDocuments = $this->accessService->getUserAccessibleDocuments($user);
        
        // Get filters from request
        $filters = [
            'document_type' => $request->input('document_type'),
            'department' => $request->input('department'),
            'access_type' => $request->input('access_type'),
            'search' => $request->input('search'),
        ];

        // Apply filters
        if ($filters['document_type']) {
            $accessibleDocuments = $accessibleDocuments->filter(function ($version) use ($filters) {
                return $version->document->document_type->value === $filters['document_type'];
            });
        }

        if ($filters['department']) {
            $accessibleDocuments = $accessibleDocuments->filter(function ($version) use ($filters) {
                return $version->document->department_id == $filters['department'];
            });
        }

        if ($filters['access_type']) {
            $accessibleDocuments = $accessibleDocuments->filter(function ($version) use ($filters, $user) {
                $accessRequest = $version->accessRequests->where('user_id', $user->id)->first();
                if (!$accessRequest) {
                    return $filters['access_type'] === 'full';
                }
                return $accessRequest->getEffectiveAccessType()->value === $filters['access_type'];
            });
        }

        if ($filters['search']) {
            $accessibleDocuments = $accessibleDocuments->filter(function ($version) use ($filters) {
                return stripos($version->document->title, $filters['search']) !== false ||
                       stripos($version->document->document_number, $filters['search']) !== false;
            });
        }

        // Convert to paginated collection
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $items = $accessibleDocuments->values();
        $total = $items->count();
        $paginatedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $accessibleDocuments = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Get filter options
        $documentTypes = DocumentType::cases();
        $departments = Department::orderBy('name')->get();
        $accessTypes = AccessType::cases();
        
        return view('document-access.my-access', compact('accessibleDocuments', 'filters', 'documentTypes', 'departments', 'accessTypes'));
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

    public function storeAccessRequest(RequestAccessRequest $request, Document $document): RedirectResponse
    {
        // Super Admin and Owner don't need to request access
        if (Auth::user()->hasRole(['Super Admin', 'Owner'])) {
            return redirect()->route('documents.show', $document)
                ->with('info', 'You have full access to all documents as an administrator.');
        }

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

    public function approve(ApproveAccessRequest $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
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

        $mimeType = MimeTypeHelper::getMimeType($version->file_type);
        
        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $version->document->title . '.' . $version->file_type . '"');
    }
}
