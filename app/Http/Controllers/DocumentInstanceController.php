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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class DocumentInstanceController extends Controller
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

        $instances = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('correspondences.index', compact('instances'));
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
}

