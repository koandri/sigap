<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentType;
use App\Models\Concerns\HasLifetimeTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AssetComponent extends Model
{
    use HasFactory;
    use HasLifetimeTracking;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'parent_asset_id',
        'component_asset_id',
        'component_type',
        'installed_date',
        'installed_usage_value',
        'disposed_usage_value',
        'removed_date',
        'removed_by',
        'removal_reason',
        'installation_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'installed_date' => 'date',
        'removed_date' => 'date',
        'component_type' => ComponentType::class,
        'installed_usage_value' => 'decimal:2',
        'disposed_usage_value' => 'decimal:2',
    ];

    /**
     * Get the parent asset.
     */
    public function parentAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    /**
     * Get the component asset.
     */
    public function componentAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'component_asset_id');
    }

    /**
     * Get the user who removed this component.
     */
    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    /**
     * Scope to get only active (not removed) components.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('removed_date');
    }

    /**
     * Scope to get only removed components.
     */
    public function scopeRemoved($query)
    {
        return $query->whereNotNull('removed_date');
    }

    /**
     * Scope to filter by component type.
     */
    public function scopeOfType($query, ComponentType $type)
    {
        return $query->where('component_type', $type);
    }

    /**
     * Check if component is currently active.
     */
    public function isActive(): bool
    {
        return $this->removed_date === null;
    }

    /**
     * Check if component has been removed.
     */
    public function isRemoved(): bool
    {
        return $this->removed_date !== null;
    }

    /**
     * Get the duration this component was installed (in days).
     */
    public function getInstalledDuration(): ?int
    {
        if (!$this->installed_date) {
            return null;
        }

        $endDate = $this->removed_date ?? now();
        return (int) $this->installed_date->diffInDays($endDate);
    }

    /**
     * For HasLifetimeTracking trait - use installed_date as the start date.
     */
    public function getLifetimeUnit(): ?string
    {
        return $this->componentAsset?->lifetime_unit;
    }

    /**
     * For HasLifetimeTracking trait - use expected lifetime from component asset.
     */
    public function getExpectedLifetimeValue(): ?float
    {
        return $this->componentAsset?->expected_lifetime_value;
    }
}
