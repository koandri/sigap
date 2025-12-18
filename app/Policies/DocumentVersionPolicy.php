<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class DocumentVersionPolicy
{
    /**
     * Determine whether the user can view the document version.
     */
    public function view(User $user, DocumentVersion $version): bool
    {
        // Super Admin, Owner, and Document Control can view any version
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Check if user has access to the document
        if (!$user->can('view', $version->document)) {
            return false;
        }

        // For active versions, check access permissions
        if ($version->isActive()) {
            return $this->checkAccessPermissions($user, $version);
        }

        // For draft versions, only creator can view
        if ($version->isDraft()) {
            return $version->created_by === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can create document versions.
     * Users from the document's department can create revisions.
     */
    public function create(User $user, Document $document): bool
    {
        // Super Admin, Owner, and Document Control can always create versions
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Check if user is in the document's department
        $userDepartment = $user->roles()->where('name', $document->department->name)->exists();
        if (!$userDepartment) {
            return false;
        }

        // Check if document type supports versions
        if (!$document->document_type->canHaveVersions()) {
            return false;
        }

        // Check if user has a manager (required for approval workflow)
        if (!$user->manager_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can edit the document version.
     * Only the creator or their manager can edit revisions.
     */
    public function edit(User $user, DocumentVersion $version): bool
    {
        // Super Admin, Owner, and Document Control can always edit
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Only creator can edit draft versions
        if ($version->isDraft()) {
            return $version->created_by === $user->id;
        }

        // Check if user is the creator
        if ($version->created_by === $user->id) {
            return true;
        }

        // Check if user is the manager of the creator
        $creator = $version->creator;
        if ($creator && $creator->manager_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the document version.
     */
    public function approve(User $user, DocumentVersion $version): bool
    {
        // Check if user has approve permission
        if (!$user->hasPermissionTo('dms.versions.approve')) {
            return false;
        }

        // Check if version is pending approval
        if (!$version->isPending()) {
            return false;
        }

        // Check approval tier
        $pendingApproval = $version->approvals()
            ->where('status', 'pending')
            ->first();

        if (!$pendingApproval) {
            return false;
        }

        // Check if user is the assigned approver
        if ($pendingApproval->approver_id === $user->id) {
            return true;
        }

        // Super Admin and Owner can approve any version
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        return false;
    }

    /**
     * Check access permissions for the version
     */
    private function checkAccessPermissions(User $user, DocumentVersion $version): bool
    {
        // If document doesn't require access request, user can view
        if (!$version->document->document_type->requiresAccessRequest()) {
            return true;
        }

        // Check if user has active access
        return $user->documentAccessRequests()
            ->where('document_version_id', $version->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('approved_expiry_date')
                      ->orWhere('approved_expiry_date', '>', now());
            })
            ->exists();
    }
}
