<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DocumentBorrowStatus;
use App\Models\Document;
use App\Models\DocumentBorrow;
use App\Models\User;
use App\Services\DocumentService;

final class DocumentBorrowPolicy
{
    public function __construct(
        private readonly DocumentService $documentService
    ) {}

    /**
     * Determine if the user can view any borrows.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('dms.borrows.view');
    }

    /**
     * Determine if the user can view a specific borrow.
     */
    public function view(User $user, DocumentBorrow $borrow): bool
    {
        // User can view their own borrows
        if ($borrow->user_id === $user->id) {
            return true;
        }

        // Super Admin and Owner can view all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Users with manage permission can view all
        return $user->hasPermissionTo('dms.borrows.manage');
    }

    /**
     * Determine if the user can create a borrow request.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dms.borrows.request');
    }

    /**
     * Determine if the user can borrow a specific document.
     */
    public function borrow(User $user, Document $document): bool
    {
        // Must have request permission
        if (!$user->hasPermissionTo('dms.borrows.request')) {
            return false;
        }

        // Must have access to the document
        return $this->documentService->checkUserCanAccess($user, $document);
    }

    /**
     * Determine if the user can approve borrow requests.
     */
    public function approve(User $user, DocumentBorrow $borrow): bool
    {
        // Only Super Admin and Owner can approve
        if (!$user->hasRole(['Super Admin', 'Owner'])) {
            return false;
        }

        // Can only approve pending requests
        return $borrow->status === DocumentBorrowStatus::Pending;
    }

    /**
     * Determine if the user can reject borrow requests.
     */
    public function reject(User $user, DocumentBorrow $borrow): bool
    {
        // Only Super Admin and Owner can reject
        if (!$user->hasRole(['Super Admin', 'Owner'])) {
            return false;
        }

        // Can only reject pending requests
        return $borrow->status === DocumentBorrowStatus::Pending;
    }

    /**
     * Determine if the user can checkout a borrow (mark as physically collected).
     */
    public function checkout(User $user, DocumentBorrow $borrow): bool
    {
        // Super Admin, Owner, or users with manage permission
        if (!$user->hasRole(['Super Admin', 'Owner']) && !$user->hasPermissionTo('dms.borrows.manage')) {
            return false;
        }

        // Can only checkout approved borrows
        return $borrow->status === DocumentBorrowStatus::Approved;
    }

    /**
     * Determine if the user can return a borrow.
     */
    public function return(User $user, DocumentBorrow $borrow): bool
    {
        // Super Admin, Owner, or users with manage permission
        if (!$user->hasRole(['Super Admin', 'Owner']) && !$user->hasPermissionTo('dms.borrows.manage')) {
            return false;
        }

        // Can only return checked out borrows
        return $borrow->status === DocumentBorrowStatus::CheckedOut;
    }

    /**
     * Determine if the user can cancel a borrow request.
     */
    public function cancel(User $user, DocumentBorrow $borrow): bool
    {
        // User can cancel their own pending requests
        if ($borrow->user_id === $user->id && $borrow->status === DocumentBorrowStatus::Pending) {
            return true;
        }

        // Super Admin and Owner can cancel any pending request
        return $user->hasRole(['Super Admin', 'Owner']) && $borrow->status === DocumentBorrowStatus::Pending;
    }

    /**
     * Determine if the user can view pending approvals.
     */
    public function viewPending(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Owner']);
    }
}

