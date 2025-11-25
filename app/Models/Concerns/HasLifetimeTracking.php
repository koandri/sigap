<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\UsageUnit;
use Carbon\Carbon;

trait HasLifetimeTracking
{
    /**
     * Calculate the lifetime percentage based on expected vs actual/current usage.
     * Returns null if expected_lifetime_value is not set.
     */
    public function getLifetimePercentage(): ?float
    {
        if (!$this->expected_lifetime_value || !$this->lifetime_unit) {
            return null;
        }

        $currentValue = $this->getCurrentLifetimeValue();
        
        if ($currentValue === null) {
            return null;
        }

        $percentage = ($currentValue / $this->expected_lifetime_value) * 100;
        
        return min(100, max(0, $percentage));
    }

    /**
     * Get the current lifetime value based on the lifetime unit.
     */
    public function getCurrentLifetimeValue(): ?float
    {
        // If disposed, use actual lifetime value
        if ($this->disposed_date && $this->actual_lifetime_value) {
            return (float) $this->actual_lifetime_value;
        }

        // If not installed yet, return 0
        if (!$this->installed_date) {
            return null;
        }

        // For time-based units, calculate days since installation
        if ($this->lifetime_unit === UsageUnit::Days) {
            return (float) Carbon::parse($this->installed_date)->diffInDays(now());
        }

        // For usage-based units, use the current usage value
        if ($this->lifetime_unit?->isUsageBased()) {
            return $this->installed_usage_value ? (float) $this->installed_usage_value : 0;
        }

        return null;
    }

    /**
     * Calculate remaining lifetime (expected - current).
     */
    public function getRemainingLifetime(): ?float
    {
        if (!$this->expected_lifetime_value) {
            return null;
        }

        $current = $this->getCurrentLifetimeValue();
        
        if ($current === null) {
            return null;
        }

        $remaining = $this->expected_lifetime_value - $current;
        
        return max(0, $remaining);
    }

    /**
     * Get formatted lifetime percentage with color coding.
     */
    public function getFormattedLifetimePercentage(): ?string
    {
        $percentage = $this->getLifetimePercentage();
        
        if ($percentage === null) {
            return null;
        }

        return number_format($percentage, 1) . '%';
    }

    /**
     * Get remaining lifetime with unit.
     */
    public function getFormattedRemainingLifetime(): ?string
    {
        $remaining = $this->getRemainingLifetime();
        
        if ($remaining === null || !$this->lifetime_unit) {
            return null;
        }

        $unit = $this->lifetime_unit->label();
        
        return number_format($remaining, 0) . ' ' . $unit;
    }

    /**
     * Check if the asset is nearing end of life (>80% of expected lifetime).
     */
    public function isNearingEndOfLife(): bool
    {
        $percentage = $this->getLifetimePercentage();
        
        return $percentage !== null && $percentage >= 80;
    }

    /**
     * Check if the asset has exceeded expected lifetime.
     */
    public function hasExceededLifetime(): bool
    {
        $percentage = $this->getLifetimePercentage();
        
        return $percentage !== null && $percentage >= 100;
    }

    /**
     * Get lifetime status color for UI.
     */
    public function getLifetimeStatusColor(): string
    {
        $percentage = $this->getLifetimePercentage();
        
        if ($percentage === null) {
            return 'gray';
        }

        if ($percentage >= 100) {
            return 'red';
        }

        if ($percentage >= 80) {
            return 'orange';
        }

        if ($percentage >= 50) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Get estimated end of life date (for time-based units only).
     */
    public function getEstimatedEndOfLifeDate(): ?Carbon
    {
        if (!$this->installed_date || !$this->expected_lifetime_value || $this->lifetime_unit !== UsageUnit::Days) {
            return null;
        }

        return Carbon::parse($this->installed_date)->addDays((int) $this->expected_lifetime_value);
    }

    /**
     * Scope to get assets nearing end of life.
     */
    public function scopeNearingEndOfLife($query)
    {
        return $query->whereNotNull('expected_lifetime_value')
            ->whereNotNull('installed_date')
            ->where(function ($q) {
                // This is a simplified version - actual calculation would need raw SQL
                $q->whereRaw('DATEDIFF(NOW(), installed_date) >= expected_lifetime_value * 0.8');
            });
    }

    /**
     * Scope to get assets that have exceeded lifetime.
     */
    public function scopeExceededLifetime($query)
    {
        return $query->whereNotNull('expected_lifetime_value')
            ->whereNotNull('installed_date')
            ->where(function ($q) {
                $q->whereRaw('DATEDIFF(NOW(), installed_date) >= expected_lifetime_value');
            });
    }
}
