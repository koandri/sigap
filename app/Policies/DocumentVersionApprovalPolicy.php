<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentVersionApproval;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class DocumentVersionApprovalPolicy
{
    /**
     * Determine whether the user can view any approvals.
     */
    public function viewAny(User $user): bool
    {
        // Users with approval permission can view pending approvals
        return $user->hasPermissionTo('dms.versions.approve') || 
               $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can view the approval.
     */
    public function view(User $user, DocumentVersionApproval $approval): bool
    {
        // Super Admin and Owner can view any approval
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // User can view approvals assigned to them
        if ($approval->approver_id === $user->id) {
            return true;
        }

        // User can view approvals for versions they created
        if ($approval->documentVersion->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the document version.
     */
    public function approve(User $user, DocumentVersionApproval $approval): bool
    {
        // Check if approval is pending
        if (!$approval->isPending()) {
            return false;
        }

        // Check if user is the assigned approver
        if ($approval->approver_id === $user->id) {
            return true;
        }

        // Super Admin and Owner can approve any version
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reject the document version.
     */
    public function reject(User $user, DocumentVersionApproval $approval): bool
    {
        return $this->approve($user, $approval);
    }
}
























