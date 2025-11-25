<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UsageUnit;
use App\Models\Asset;
use Illuminate\Support\Facades\Log;

final class AssetLifetimeService
{
    /**
     * Calculate average lifetime for a category.
     */
    public function calculateAverageLifetime(int $categoryId, ?UsageUnit $unit = null): ?float
    {
        $query = Asset::where('asset_category_id', $categoryId)
            ->whereNotNull('disposed_date')
            ->whereNotNull('actual_lifetime_value');

        if ($unit !== null) {
            $query->where('lifetime_unit', $unit->value);
        }

        return (float) $query->avg('actual_lifetime_value');
    }

    /**
     * Get expected lifetime for an asset.
     */
    public function getExpectedLifetime(Asset $asset): ?float
    {
        if ($asset->expected_lifetime_value === null) {
            return null;
        }
        
        return (float) $asset->expected_lifetime_value;
    }

    /**
     * Calculate actual lifetime for an asset.
     */
    public function calculateActualLifetime(Asset $asset): ?float
    {
        // If disposed, calculate final lifetime
        if ($asset->disposed_date) {
            $unit = $asset->lifetime_unit;

            // Usage-based calculation
            if ($unit && $unit->isUsageBased()) {
                if ($asset->installed_usage_value !== null && $asset->disposed_usage_value !== null) {
                    return $asset->disposed_usage_value - $asset->installed_usage_value;
                }
                // If we have actual_lifetime_value stored, return it
                if ($asset->actual_lifetime_value !== null) {
                    return (float) $asset->actual_lifetime_value;
                }
                return null;
            }

            // Date-based calculation
            $startDate = $asset->installed_date ?? $asset->purchase_date;
            if (!$startDate) {
                return null;
            }

            return (float) $startDate->diffInDays($asset->disposed_date);
        }

        // If active (not disposed)
        $unit = $asset->lifetime_unit;
        
        // For usage-based, we can't know current lifetime without logs
        if (!$unit || $unit->isUsageBased()) {
            return null;
        }

        // For date-based, we can calculate current age
        $startDate = $asset->installed_date ?? $asset->purchase_date;
        if (!$startDate) {
            return null;
        }

        return (float) $startDate->diffInDays(now());
    }

    /**
     * Update asset lifetime on disposal.
     */
    public function updateAssetLifetimeOnDisposal(Asset $asset): void
    {
        try {
            $actualLifetime = $this->calculateActualLifetime($asset);

            if ($actualLifetime !== null) {
                $asset->update([
                    'actual_lifetime_value' => $actualLifetime,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update asset lifetime on disposal: " . $e->getMessage());
        }
    }

    /**
     * Calculate lifetime percentage (for active assets).
     */
    public function getLifetimePercentage(Asset $asset): ?float
    {
        return $asset->getLifetimePercentage();
    }

    /**
     * Calculate remaining lifetime (for active assets).
     */
    public function getRemainingLifetime(Asset $asset): ?float
    {
        return $asset->getRemainingLifetime();
    }

    /**
     * Suggest expected lifetime based on category averages.
     */
    public function suggestExpectedLifetime(Asset $asset): ?float
    {
        $unit = $asset->lifetime_unit;
        if (!$unit) {
            return null;
        }

        return $asset->assetCategory->getAverageLifetime($unit);
    }
}
