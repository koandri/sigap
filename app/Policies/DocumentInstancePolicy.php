<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentInstance;
use App\Models\User;

final class DocumentInstancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('dms.instances.view');
    }

    public function view(User $user, DocumentInstance $instance): bool
    {
        // Creator can always view
        if ($instance->created_by === $user->id) {
            return true;
        }

        // Super Admin and Owner can view all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        return $user->hasPermissionTo('dms.instances.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('dms.instances.create');
    }

    public function update(User $user, DocumentInstance $instance): bool
    {
        // Only creator can update, and only if status is draft
        return $instance->created_by === $user->id && 
               $instance->canBeEdited() &&
               $user->hasPermissionTo('dms.instances.edit');
    }

    public function delete(User $user, DocumentInstance $instance): bool
    {
        // Only creator or admin can delete
        return ($instance->created_by === $user->id || $user->hasRole(['Super Admin', 'Owner'])) &&
               $user->hasPermissionTo('dms.instances.delete');
    }

    public function approveAny(User $user): bool
    {
        // Super Admin and Owner can approve any instance
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Check if user has the approve permission
        return $user->hasPermissionTo('dms.instances.approve');
    }

    public function approve(User $user, DocumentInstance $instance): bool
    {
        // Super Admin, Owner, or user's manager can approve
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        // Check if user is the creator's manager
        $creator = $instance->creator;
        if ($creator && $creator->manager_id === $user->id) {
            return true;
        }

        return $user->hasPermissionTo('dms.instances.approve');
    }

    public function downloadPdf(User $user, DocumentInstance $instance): bool
    {
        // Creator can download
        if ($instance->created_by === $user->id) {
            return true;
        }

        // Super Admin and Owner can download
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return true;
        }

        return false;
    }
}


