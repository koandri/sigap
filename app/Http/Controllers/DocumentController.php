<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\Department;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);
        
        $user = auth()->user();
        $documents = $this->documentService->getDocumentsAccessibleByUser($user);
        
        $filters = [
            'department' => $request->get('department'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        // Apply filters
        if ($filters['department']) {
            $documents = $documents->filter(function ($document) use ($filters) {
                return $document->department_id == $filters['department'];
            });
        }

        if ($filters['type']) {
            $documents = $documents->filter(function ($document) use ($filters) {
                return $document->document_type->value === $filters['type'];
            });
        }

        if ($filters['search']) {
            $documents = $documents->filter(function ($document) use ($filters) {
                return stripos($document->title, $filters['search']) !== false ||
                       stripos($document->document_number, $filters['search']) !== false;
            });
        }

        // Convert to paginated collection
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $items = $documents->values();
        $total = $items->count();
        $paginatedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $documents = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $departments = Department::all();
        $documentTypes = DocumentType::cases();

        return view('documents.index', compact('documents', 'departments', 'documentTypes', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Document::class);
        
        $user = auth()->user();
        
        $departments = Department::all();
        $documentTypes = DocumentType::cases();
        
        // Get available correspondence templates (InternalMemo and OutgoingLetter with active versions)
        $correspondenceTemplates = Document::whereIn('document_type', [
            DocumentType::InternalMemo,
            DocumentType::OutgoingLetter,
        ])
            ->whereHas('activeVersion')
            ->with(['activeVersion', 'department', 'creator'])
            ->get()
            ->filter(function ($document) use ($user) {
                return $this->documentService->checkUserCanAccess($user, $document);
            })
            ->values();
        
        return view('documents.create', compact('departments', 'documentTypes', 'correspondenceTemplates'));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->only([
            'document_number',
            'title',
            'description',
            'document_type',
            'department_id',
            'physical_location',
        ]);
        
        $data['created_by'] = auth()->id();

        $document = $this->documentService->createDocument($data);

        if ($request->has('accessible_departments')) {
            $this->documentService->assignAccessibleDepartments($document, $request->accessible_departments);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
    }

    public function show(Document $document): View
    {
        $user = auth()->user();
        
        if (!$this->documentService->checkUserCanAccess($user, $document)) {
            abort(403, 'You do not have access to this document.');
        }

        $document->load(['department', 'creator', 'versions.creator', 'accessibleDepartments']);
        
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);
        
        $departments = Department::all();
        $documentTypes = DocumentType::cases();
        
        return view('documents.edit', compact('document', 'departments', 'documentTypes'));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $request->only([
            'title',
            'description',
            'document_type',
            'department_id',
            'physical_location',
        ]);

        $this->documentService->updateDocument($document, $data);

        if ($request->has('accessible_departments')) {
            $this->documentService->assignAccessibleDepartments($document, $request->accessible_departments);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);
        
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
