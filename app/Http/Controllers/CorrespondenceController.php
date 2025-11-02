<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentInstance;
use App\Models\DocumentVersion;
use App\Services\DocumentInstanceService;
use App\Services\DocumentService;
use App\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class CorrespondenceController extends Controller
{
    public function __construct(
        private readonly DocumentInstanceService $instanceService,
        private readonly DocumentService $documentService,
        private readonly OnlyOfficeService $onlyOfficeService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DocumentInstance::class);
        
        $user = Auth::user();
        $query = DocumentInstance::with(['templateVersion.document', 'creator', 'approver']);

        // Filter by creator if not admin
        if (!$user->hasRole(['Super Admin', 'Owner'])) {
            $query->where('created_by', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Order by: pending approvals first (if user can approve), then by created_at desc
        $instances = $query->get();
        
        // Separate pending approvals if user can approve them
        $pendingApprovals = collect();
        $otherInstances = collect();
        
        foreach ($instances as $instance) {
            if ($instance->status->value === 'pending_approval' && $user->can('approve', $instance)) {
                $pendingApprovals->push($instance);
            } else {
                $otherInstances->push($instance);
            }
        }
        
        // Sort pending approvals by created_at desc
        $pendingApprovals = $pendingApprovals->sortByDesc('created_at');
        // Sort other instances by created_at desc
        $otherInstances = $otherInstances->sortByDesc('created_at');
        
        // Merge: pending approvals first, then others
        $sortedInstances = $pendingApprovals->merge($otherInstances);
        
        // Paginate manually
        $currentPage = $request->get('page', 1);
        $perPage = 20;
        $total = $sortedInstances->count();
        $paginatedItems = $sortedInstances->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $instances = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Count pending approvals for this user
        $pendingApprovalsCount = $pendingApprovals->count();

        return view('correspondences.index', compact('instances', 'pendingApprovalsCount'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', DocumentInstance::class);
        
        $user = Auth::user();
        
        // Get all templates (documents with type InternalMemo or OutgoingLetter that have active versions)
        $allTemplates = Document::whereIn('document_type', [
            DocumentType::InternalMemo,
            DocumentType::OutgoingLetter,
        ])
            ->whereHas('activeVersion')
            ->with(['activeVersion', 'department', 'creator'])
            ->get();
        
        // Filter by user permissions - user must be able to view the document to use it as template
        $templates = $allTemplates->filter(function ($document) use ($user) {
            return $this->documentService->checkUserCanAccess($user, $document);
        });
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $templates = $templates->filter(function ($template) use ($search) {
                return str_contains(strtolower($template->title), $search) ||
                       str_contains(strtolower($template->document_number), $search) ||
                       str_contains(strtolower($template->department->name ?? ''), $search);
            });
        }
        
        // Type filter
        if ($request->has('type') && $request->type) {
            $templates = $templates->filter(function ($template) use ($request) {
                return $template->document_type->value === $request->type;
            });
        }
        
        // Reset array keys for proper pagination/counting
        $templates = $templates->values();
        
        $selectedTemplateId = $request->get('template_id');

        return view('correspondences.create', compact('templates', 'selectedTemplateId'));
    }

    public function store(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('view', $document);

        // Verify document is a template type
        if (!$document->document_type->isTemplate()) {
            return redirect()->back()
                ->with('error', 'Only Internal Memo and Outgoing Letter documents can be used as templates.');
        }

        $activeVersion = $document->activeVersion;
        if (!$activeVersion) {
            return redirect()->back()
                ->with('error', 'The selected template does not have an active version.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'content_summary' => 'nullable|string|max:2000',
        ]);

        $instance = $this->instanceService->createInstance(
            $activeVersion,
            Auth::user(),
            $request->only(['subject', 'content_summary'])
        );

        return redirect()->route('correspondences.show', $instance)
            ->with('success', 'Correspondence created successfully. You can now edit it.');
    }

    public function show(DocumentInstance $instance): View
    {
        $this->authorize('view', $instance);
        
        $instance->load([
            'templateVersion.document',
            'creator',
            'approver'
        ]);

        return view('correspondences.show', compact('instance'));
    }

    public function edit(DocumentInstance $instance): View|RedirectResponse
    {
        $this->authorize('update', $instance);

        if (!$instance->canBeEdited()) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'This correspondence cannot be edited. Only draft correspondence created by you can be edited.');
        }

        // Load the template version relationship
        $instance->load('templateVersion.document');
        $templateVersion = $instance->templateVersion;

        if (!$templateVersion) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'Template version not found for this correspondence.');
        }

        // If file_path is empty, create the initial empty document
        if (empty($templateVersion->file_path)) {
            try {
                $this->onlyOfficeService->createDocument($templateVersion, $templateVersion->file_type ?? 'docx');
                $templateVersion->refresh();
            } catch (\Exception $e) {
                return redirect()->route('correspondences.show', $instance)
                    ->with('error', 'Unable to create initial document. Error: ' . $e->getMessage());
            }
        } else {
            // Check if the file exists in storage
            try {
                if (!Storage::disk('s3')->exists($templateVersion->file_path)) {
                    return redirect()->route('correspondences.show', $instance)
                        ->with('error', 'The document file does not exist in storage. The file may have been deleted or moved.');
                }
            } catch (\Exception $e) {
                return redirect()->route('correspondences.show', $instance)
                    ->with('error', 'Unable to access storage. Please check your S3 configuration. Error: ' . $e->getMessage());
            }
        }

        // Get OnlyOffice config for editing the template
        $editorConfig = $this->onlyOfficeService->getEditorConfig($templateVersion);

        return view('correspondences.edit', compact('instance', 'editorConfig'));
    }

    public function update(Request $request, DocumentInstance $instance): RedirectResponse
    {
        $this->authorize('update', $instance);

        if (!$instance->canBeEdited()) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'This correspondence cannot be edited.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'content_summary' => 'nullable|string|max:2000',
        ]);

        $this->instanceService->updateInstance($instance, $request->only(['subject', 'content_summary']));

        return redirect()->route('correspondences.show', $instance)
            ->with('success', 'Correspondence updated successfully.');
    }

    public function submitForApproval(DocumentInstance $instance): RedirectResponse
    {
        $this->authorize('update', $instance);

        if (!$instance->canBeEdited()) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'Only draft correspondence can be submitted for approval.');
        }

        $this->instanceService->submitForApproval($instance);

        return redirect()->route('correspondences.show', $instance)
            ->with('success', 'Correspondence submitted for approval.');
    }

    public function approve(Request $request, DocumentInstance $instance): RedirectResponse
    {
        $this->authorize('approve', $instance);

        $this->instanceService->approveInstance($instance, Auth::user());

        return redirect()->route('correspondences.show', $instance)
            ->with('success', 'Correspondence approved successfully.');
    }

    public function reject(DocumentInstance $instance): RedirectResponse
    {
        $this->authorize('approve', $instance);

        $this->instanceService->rejectInstance($instance);

        return redirect()->route('correspondences.show', $instance)
            ->with('success', 'Correspondence rejected and returned to draft status.');
    }

    public function downloadPdf(DocumentInstance $instance): StreamedResponse|RedirectResponse
    {
        $this->authorize('downloadPdf', $instance);

        if (!$instance->isApproved()) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'PDF is only available for approved correspondence.');
        }

        // Load template version
        $instance->load('templateVersion');
        
        if (!$instance->templateVersion || !$instance->templateVersion->file_path) {
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'Template document not found. Please contact administrator.');
        }

        try {
            // Convert the template version document to PDF via OnlyOffice
            // This will preserve all images and formatting
            $tempPdfPath = $this->onlyOfficeService->convertToPDF($instance->templateVersion);
            
            if (!Storage::disk('s3')->exists($tempPdfPath)) {
                \Log::error('PDF conversion file not found', [
                    'instance_id' => $instance->id,
                    'pdf_path' => $tempPdfPath,
                ]);
                
                return redirect()->route('correspondences.show', $instance)
                    ->with('error', 'Failed to generate PDF. The converted file was not found. Please check server logs.');
            }

            // Read the PDF content from storage
            $pdfContent = Storage::disk('s3')->get($tempPdfPath);
            
            // Verify it's actually a PDF
            if (substr($pdfContent, 0, 4) !== '%PDF') {
                \Log::error('Generated file is not a valid PDF', [
                    'instance_id' => $instance->id,
                    'pdf_path' => $tempPdfPath,
                    'file_start' => substr($pdfContent, 0, 50),
                ]);
                
                return redirect()->route('correspondences.show', $instance)
                    ->with('error', 'PDF generation failed: Invalid PDF file generated. Please check server logs.');
            }
            
            // Clean up the temporary PDF file (delete it)
            try {
                Storage::disk('s3')->delete($tempPdfPath);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete temporary PDF file', [
                    'instance_id' => $instance->id,
                    'pdf_path' => $tempPdfPath,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if cleanup fails
            }

            $filename = Str::slug($instance->instance_number) . '.pdf';
            
            // Stream the PDF directly to the user without saving
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('correspondences.show', $instance)
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage() . '. Please check OnlyOffice server configuration or contact administrator.');
        }
    }
}

