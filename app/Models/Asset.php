<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Asset extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'code',
        'asset_category_id',
        'location',
        'purchase_date',
        'warranty_expiry',
        'serial_number',
        'manufacturer',
        'model',
        'status',
        'specifications',
        'image_path',
        'department_id',
        'user_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the asset.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
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
     * Get all documents for this asset.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    /**
     * Scope to get only active assets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('asset_category_id', $categoryId);
    }
}