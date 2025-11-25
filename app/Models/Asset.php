<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentType;
use App\Enums\UsageUnit;
use App\Models\Concerns\HasFiles;
use App\Models\Concerns\HasLifetimeTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

final class Asset extends Model
{
    use HasFactory;
    use HasLifetimeTracking;
    use HasFiles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_code',
        'name',
        'asset_category_id',
        'location_id',
        'department_id',
        'user_id',
        'purchase_date',
        'purchase_price',
        'warranty_expiry',
        'serial_number',
        'status',
        'specifications',
        'qr_code_path',
        'disposed_date',
        'disposed_by',
        'disposal_reason',
        'disposal_work_order_id',
        'installed_date',
        'installed_usage_value',
        'disposed_usage_value',
        'usage_type_id',
        'lifetime_unit',
        'expected_lifetime_value',
        'actual_lifetime_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'disposed_date' => 'date',
        'installed_date' => 'date',
        'specifications' => \App\ValueObjects\AssetSpecifications::class,
        'installed_usage_value' => 'decimal:2',
        'disposed_usage_value' => 'decimal:2',
        'expected_lifetime_value' => 'integer',
        'actual_lifetime_value' => 'integer',
        'component_type' => ComponentType::class,
        'lifetime_unit' => UsageUnit::class,
    ];

    /**
     * Get the category that owns the asset.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get the usage type for this asset.
     */
    public function usageType(): BelongsTo
    {
        return $this->belongsTo(AssetCategoryUsageType::class, 'usage_type_id');
    }

    /**
     * Get the location that owns the asset.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the department that owns the asset.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user assigned to the asset.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all maintenance schedules for this asset.
     */
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    /**
     * Get all work orders for this asset.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get all maintenance logs for this asset.
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Get the user who disposed this asset.
     */
    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    /**
     * Get the work order that led to disposal.
     */
    public function disposalWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'disposal_work_order_id');
    }

    /**
     * Get all component relationships where this asset is the parent.
     */
    public function componentRelationships(): HasMany
    {
        return $this->hasMany(AssetComponent::class, 'parent_asset_id');
    }

    /**
     * Get all active components for this asset.
     */
    public function activeComponents(): HasMany
    {
        return $this->hasMany(AssetComponent::class, 'parent_asset_id')
            ->active()
            ->with('componentAsset');
    }

    /**
     * Get all component relationships where this asset is the component.
     */
    public function parentRelationships(): HasMany
    {
        return $this->hasMany(AssetComponent::class, 'component_asset_id');
    }

    /**
     * Get all active parent relationships for this asset.
     */
    public function activeParentRelationships(): HasMany
    {
        return $this->hasMany(AssetComponent::class, 'component_asset_id')
            ->active()
            ->with('parentAsset');
    }

    /**
     * Get the first active parent asset (where this asset is a component).
     * Uses hasOneThrough to get the parent Asset through the AssetComponent pivot table.
     */
    public function parentAsset(): HasOneThrough
    {
        return $this->hasOneThrough(
            Asset::class,           // Final model (parent asset)
            AssetComponent::class,  // Intermediate model (pivot)
            'component_asset_id',   // Foreign key on AssetComponent pointing to this Asset
            'id',                   // Foreign key on Asset (parent) pointing to AssetComponent
            'id',                   // Local key on this Asset
            'parent_asset_id'       // Local key on AssetComponent pointing to parent Asset
        )->whereNull('asset_components.removed_date') // Only active components
         ->latest('asset_components.installed_date'); // Get the most recently installed one
    }

    /**
     * Get all child assets (components) for this asset.
     * Uses hasManyThrough to get the Asset models through the AssetComponent pivot table.
     */
    public function childAssets(): HasManyThrough
    {
        return $this->hasManyThrough(
            Asset::class,           // Final model (child/component asset)
            AssetComponent::class,  // Intermediate model (pivot)
            'parent_asset_id',      // Foreign key on AssetComponent pointing to this Asset (parent)
            'id',                   // Foreign key on Asset (child) pointing to AssetComponent
            'id',                   // Local key on this Asset
            'component_asset_id'    // Local key on AssetComponent pointing to child Asset
        )->whereNull('asset_components.removed_date'); // Only active components
    }

    /**
     * Get the full URL for the QR code.
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->qr_code_path) {
            return null;
        }

        return Storage::disk('s3')->url($this->qr_code_path);
    }

    /**
     * Scope to get only active assets (not disposed).
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'disposed');
    }

    /**
     * Scope to get only disposed assets.
     */
    public function scopeDisposed($query)
    {
        return $query->where('status', 'disposed');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('asset_category_id', $categoryId);
    }

    /**
    }

    /**
     * Check if asset is a component (has active parent relationships).
     */
    public function isComponent(): bool
    {
        return $this->activeParentRelationships()->exists();
    }

    /**
     * Check if asset has child components.
     */
    public function hasComponents(): bool
    {
        return $this->activeComponents()->exists();
    }

    /**
     * Scope to get only assets that are components.
     */
    public function scopeComponents($query)
    {
        return $query->whereHas('activeParentRelationships');
    }

    /**
     * Scope to get only assets that have components.
     */
    public function scopeWithComponents($query)
    {
        return $query->whereHas('activeComponents');
    }

    /**
     * Get all parent assets (where this asset is a component).
     */
    public function getParentAssets(): \Illuminate\Support\Collection
    {
        return $this->activeParentRelationships()
            ->with('parentAsset')
            ->get()
            ->pluck('parentAsset');
    }

    /**
     * Get all component assets (child assets).
     */
    public function getComponentAssets(): \Illuminate\Support\Collection
    {
        return $this->activeComponents()
            ->with('componentAsset')
            ->get()
            ->pluck('componentAsset');
    }
}