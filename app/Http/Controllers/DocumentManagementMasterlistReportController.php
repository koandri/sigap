<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Role;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DocumentManagementMasterlistReportController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('dms.reports.view');
        
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

    public function print(Request $request): View
    {
        $this->authorize('dms.reports.view');
        
        $filters = [
            'department' => $request->get('department'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        $masterlist = $this->documentService->getDocumentMasterlist($filters);
        
        return view('documents.masterlist-print', compact('masterlist', 'filters'));
    }
}

