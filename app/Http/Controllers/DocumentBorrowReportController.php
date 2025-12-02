<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentBorrowStatus;
use App\Models\DocumentBorrow;
use App\Services\DocumentBorrowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DocumentBorrowReportController extends Controller
{
    public function __construct(
        private readonly DocumentBorrowService $borrowService
    ) {}

    /**
     * Display borrowed documents report.
     */
    public function borrowedDocuments(Request $request): View
    {
        $status = $request->get('status', 'checked_out');
        $search = $request->get('search');

        $query = DocumentBorrow::with(['document', 'user', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status === 'checked_out') {
            $query->checkedOut();
        } elseif ($status === 'all_active') {
            $query->active();
        } elseif ($status === 'returned') {
            $query->returned();
        } elseif ($status === 'all') {
            // No filter
        } else {
            $query->checkedOut();
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', function ($docQuery) use ($search) {
                    $docQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $borrows = $query->paginate(20);
        $stats = $this->borrowService->getStatistics();

        $filters = [
            'status' => $status,
            'search' => $search,
        ];

        return view('reports.document-borrows.borrowed', compact('borrows', 'stats', 'filters'));
    }

    /**
     * Display overdue documents report.
     */
    public function overdueDocuments(Request $request): View
    {
        $search = $request->get('search');

        $query = DocumentBorrow::with(['document', 'user', 'approver'])
            ->overdue()
            ->orderBy('due_date', 'asc');

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('document', function ($docQuery) use ($search) {
                    $docQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $borrows = $query->paginate(20);
        $stats = $this->borrowService->getStatistics();

        $filters = [
            'search' => $search,
        ];

        return view('reports.document-borrows.overdue', compact('borrows', 'stats', 'filters'));
    }
}

