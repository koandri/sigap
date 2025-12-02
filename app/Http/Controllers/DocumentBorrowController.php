<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentBorrowStatus;
use App\Http\Requests\StoreBorrowRequest;
use App\Models\Document;
use App\Models\DocumentBorrow;
use App\Services\DocumentBorrowService;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class DocumentBorrowController extends Controller
{
    public function __construct(
        private readonly DocumentBorrowService $borrowService,
        private readonly DocumentService $documentService
    ) {}

    /**
     * Display user's borrow history.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->get('status');

        $query = DocumentBorrow::with(['document', 'approver'])
            ->byUser($user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $borrows = $query->paginate(15);
        $statuses = DocumentBorrowStatus::cases();

        return view('document-borrows.index', compact('borrows', 'statuses', 'status'));
    }

    /**
     * Show the form for creating a new borrow request.
     */
    public function create(): View
    {
        $this->authorize('create', DocumentBorrow::class);

        $user = Auth::user();
        $documents = $this->borrowService->getAvailableDocumentsForUser($user);

        // Default due date is 7 days from now
        $defaultDueDate = now()->addDays(7)->format('Y-m-d');

        return view('document-borrows.create', compact('documents', 'defaultDueDate'));
    }

    /**
     * Store a newly created borrow request.
     */
    public function store(StoreBorrowRequest $request): RedirectResponse
    {
        $document = Document::findOrFail($request->document_id);
        
        $this->authorize('borrow', $document);

        $borrow = $this->borrowService->createBorrowRequest(
            $document,
            Auth::user(),
            $request->validated()
        );

        $message = Auth::user()->hasRole(['Super Admin', 'Owner'])
            ? 'Borrow request created and auto-approved. You can now collect the document.'
            : 'Borrow request submitted successfully. Please wait for approval.';

        return redirect()
            ->route('document-borrows.show', $borrow)
            ->with('success', $message);
    }

    /**
     * Display the specified borrow.
     */
    public function show(DocumentBorrow $borrow): View
    {
        $this->authorize('view', $borrow);

        $borrow->load(['document', 'user', 'approver']);

        return view('document-borrows.show', compact('borrow'));
    }

    /**
     * Display pending borrow requests (for approvers).
     */
    public function pending(): View
    {
        $this->authorize('viewPending', DocumentBorrow::class);

        $pendingBorrows = $this->borrowService->getPendingRequests();

        return view('document-borrows.pending', compact('pendingBorrows'));
    }

    /**
     * Approve a borrow request.
     */
    public function approve(DocumentBorrow $borrow): RedirectResponse
    {
        $this->authorize('approve', $borrow);

        $this->borrowService->approveBorrowRequest($borrow, Auth::user());

        return redirect()
            ->route('document-borrows.pending')
            ->with('success', 'Borrow request approved successfully.');
    }

    /**
     * Reject a borrow request.
     */
    public function reject(Request $request, DocumentBorrow $borrow): RedirectResponse
    {
        $this->authorize('reject', $borrow);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $this->borrowService->rejectBorrowRequest(
            $borrow,
            Auth::user(),
            $request->rejection_reason
        );

        return redirect()
            ->route('document-borrows.pending')
            ->with('success', 'Borrow request rejected.');
    }

    /**
     * Mark a borrow as checked out (physically collected).
     */
    public function checkout(DocumentBorrow $borrow): RedirectResponse
    {
        $this->authorize('checkout', $borrow);

        $this->borrowService->checkoutDocument($borrow);

        return redirect()
            ->route('document-borrows.show', $borrow)
            ->with('success', 'Document marked as checked out.');
    }

    /**
     * Mark a borrow as returned.
     */
    public function returnDocument(DocumentBorrow $borrow): RedirectResponse
    {
        $this->authorize('return', $borrow);

        $this->borrowService->returnDocument($borrow);

        return redirect()
            ->route('document-borrows.show', $borrow)
            ->with('success', 'Document marked as returned.');
    }

    /**
     * Cancel a pending borrow request.
     */
    public function cancel(DocumentBorrow $borrow): RedirectResponse
    {
        $this->authorize('cancel', $borrow);

        $borrow->update([
            'status' => DocumentBorrowStatus::Rejected,
            'rejection_reason' => 'Cancelled by user',
        ]);

        return redirect()
            ->route('document-borrows.index')
            ->with('success', 'Borrow request cancelled.');
    }

    /**
     * Show review form for a pending borrow request.
     */
    public function review(DocumentBorrow $borrow): View
    {
        $this->authorize('approve', $borrow);

        $borrow->load(['document', 'user']);

        return view('document-borrows.review', compact('borrow'));
    }
}

