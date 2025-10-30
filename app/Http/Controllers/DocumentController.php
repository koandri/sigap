<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Role;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

final class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $documents = $this->documentService->getDocumentsAccessibleByUser($user);
        
        $filters = [
            'department' => $request->get('department'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        if ($filters['department']) {
            $documents = $documents->where('department_id', $filters['department']);
        }

        if ($filters['type']) {
            $documents = $documents->where('document_type', $filters['type']);
        }

        if ($filters['search']) {
            $documents = $documents->filter(function ($document) use ($filters) {
                return stripos($document->title, $filters['search']) !== false ||
                       stripos($document->document_number, $filters['search']) !== false;
            });
        }

        $departments = Role::all();
        $documentTypes = DocumentType::cases();

        return view('documents.index', compact('documents', 'departments', 'documentTypes', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Document::class);
        
        $departments = Role::whereNotIn('name', [
            'Super Admin',
            'Owner',
            'Engineering Operator',
            'User',
            'Manager',
            'Cleaner'
        ])->get();
        $documentTypes = DocumentType::cases();
        
        return view('documents.create', compact('departments', 'documentTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Document::class);
        
        $request->validate([
            'document_number' => 'required|string|unique:documents,document_number',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string',
            'department_id' => 'required|exists:roles,id',
            'physical_location' => 'nullable|array',
            'physical_location.room_no' => 'nullable|string',
            'physical_location.shelf_no' => 'nullable|string',
            'physical_location.folder_no' => 'nullable|string',
            'accessible_departments' => 'nullable|array',
            'accessible_departments.*' => 'exists:roles,id',
        ]);

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
        
        $departments = Role::whereNotIn('name', [
            'Super Admin',
            'Owner',
            'Engineering Operator',
            'User',
            'Manager',
            'Cleaner'
        ])->get();
        $documentTypes = DocumentType::cases();
        
        return view('documents.edit', compact('document', 'departments', 'documentTypes'));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string',
            'department_id' => 'required|exists:roles,id',
            'physical_location' => 'nullable|array',
            'physical_location.room_no' => 'nullable|string',
            'physical_location.shelf_no' => 'nullable|string',
            'physical_location.folder_no' => 'nullable|string',
            'accessible_departments' => 'nullable|array',
            'accessible_departments.*' => 'exists:roles,id',
        ]);

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

    public function masterlist(Request $request): View
    {
        $filters = [
            'department' => $request->get('department'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        $masterlist = $this->documentService->getDocumentMasterlist($filters);
        
        $departments = Role::all();
        $documentTypes = DocumentType::cases();
        
        return view('documents.masterlist', compact('masterlist', 'departments', 'documentTypes', 'filters'));
    }

    public function masterlistPrint(Request $request): View
    {
        $filters = [
            'department' => $request->get('department'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        $masterlist = $this->documentService->getDocumentMasterlist($filters);
        
        return view('documents.masterlist-print', compact('masterlist', 'filters'));
    }
}
