<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        // Check if user has permission to view documents
        return $user->hasPermissionTo('dms.documents.view') || 
               $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // Super Admin and Owner can view any document
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Check if user's department has access
        $hasDepartmentAccess = $document->department_id === $user->role_id ||
            $document->accessibleDepartments()->where('department_id', $user->role_id)->exists();

        if (!$hasDepartmentAccess) {
            return false;
        }

        // For documents that require access requests, check if user has active access
        if ($document->document_type->requiresAccessRequest()) {
            return $this->hasActiveAccess($user, $document);
        }

        return true;
    }

    /**
     * Determine whether the user can create documents.
     * Only Super Admin, Owner, and Document Control can create documents.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can update the document.
     * Only Super Admin, Owner, and Document Control can update documents.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can delete the document.
     * Only Super Admin, Owner, and Document Control can delete documents.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole(['Super Admin', 'Owner', 'Document Control']);
    }

    /**
     * Determine whether the user can request access to the document.
     */
    public function requestAccess(User $user, Document $document): bool
    {
        // Super Admin and Owner don't need to request access
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return false;
        }

        // Check if user has request permission
        if (!$user->hasPermissionTo('dms.access.request')) {
            return false;
        }

        // Check if document requires access request
        if (!$document->document_type->requiresAccessRequest()) {
            return false;
        }

        // Check if user already has active access
        if ($this->hasActiveAccess($user, $document)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has active access to the document
     */
    private function hasActiveAccess(User $user, Document $document): bool
    {
        $activeVersion = $document->activeVersion;
        if (!$activeVersion) {
            return false;
        }

        return $user->documentAccessRequests()
            ->where('document_version_id', $activeVersion->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('approved_expiry_date')
                      ->orWhere('approved_expiry_date', '>', now());
            })
            ->exists();
    }
}
