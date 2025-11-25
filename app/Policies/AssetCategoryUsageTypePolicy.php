<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetCategoryUsageType;
use App\Models\User;

final class AssetCategoryUsageTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('asset-categories.view') || $user->hasRole(['Super Admin', 'IT Staff']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AssetCategoryUsageType $usageType): bool
    {
        return $user->hasPermissionTo('asset-categories.view') || $user->hasRole(['Super Admin', 'IT Staff']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('asset-categories.update') || $user->hasRole(['Super Admin', 'IT Staff']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AssetCategoryUsageType $usageType): bool
    {
        return $user->hasPermissionTo('asset-categories.update') || $user->hasRole(['Super Admin', 'IT Staff']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AssetCategoryUsageType $usageType): bool
    {
        return $user->hasPermissionTo('asset-categories.update') || $user->hasRole(['Super Admin', 'IT Staff']);
    }

    /**
     * Determine whether the user can recalculate metrics.
     */
    public function recalculateMetrics(User $user, AssetCategoryUsageType $usageType): bool
    {
        return $user->hasPermissionTo('asset-categories.update') || $user->hasRole(['Super Admin', 'IT Staff']);
    }
}
