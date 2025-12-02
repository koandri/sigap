<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

final class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('options.assets.view') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('options.assets.view') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('options.assets.create') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('options.assets.update') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool
    {
        // Prevent deletion if asset has child components
        if ($asset->hasComponents()) {
            return false;
        }

        return $user->hasPermissionTo('options.assets.delete') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can attach a component.
     */
    public function attachComponent(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('options.assets.update') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can detach a component.
     */
    public function detachComponent(User $user, Asset $component): bool
    {
        return $user->hasPermissionTo('options.assets.update') || $user->hasRole(['Super Admin', 'Owner']);
    }

    /**
     * Determine whether the user can view lifetime metrics.
     */
    public function viewLifetimeMetrics(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo('options.assets.view') || $user->hasRole(['Super Admin', 'Owner']);
    }
}
