<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentAccessRequest;
use App\Models\User;

final class DocumentAccessRequestPolicy
{
    /**
     * Determine whether the user can view the access request.
     */
    public function view(User $user, DocumentAccessRequest $accessRequest): bool
    {
        // Super Admin and Owner can view any access request
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // User can view their own access requests
        if ($accessRequest->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create access requests.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dms.access.request');
    }

    /**
     * Determine whether the user can approve any access requests.
     */
    public function approveAny(User $user): bool
    {
        // Only Super Admin and Owner can approve access requests
        return $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can approve access requests.
     */
    public function approve(User $user, DocumentAccessRequest $accessRequest): bool
    {
        // Only Super Admin and Owner can approve access requests
        if (! $user->hasRole(['Super Admin', 'Owner'])) {
            return false;
        }

        // Check if access request is pending
        if (! $accessRequest->isPending()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can reject access requests.
     */
    public function reject(User $user, DocumentAccessRequest $accessRequest): bool
    {
        return $this->approve($user, $accessRequest);
    }
}
