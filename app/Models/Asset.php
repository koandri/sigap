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
        'location_id',
        'purchase_date',
        'warranty_expiry',
        'serial_number',
        'manufacturer',
        'model',
        'status',
        'specifications',
        'qr_code_path',
        'department_id',
        'user_id',
        'is_active',
        'disposed_date',
        'disposal_reason',
        'disposed_by',
        'disposal_work_order_id',
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
     * Get all documents for this asset.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    /**
     * Get all photos for this asset.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(AssetPhoto::class);
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
     * Scope to get only active assets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get disposed assets.
     */
    public function scopeDisposed($query)
    {
        return $query->where('status', 'disposed')->orWhere('is_active', false);
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

    /**
     * Get the primary photo or first photo.
     */
    public function primaryPhoto(): ?AssetPhoto
    {
        return $this->photos()->where('is_primary', true)->first()
            ?? $this->photos()->orderBy('created_at')->first();
    }

    /**
     * Get the image path from the primary photo.
     */
    public function getImagePath(): ?string
    {
        $primaryPhoto = $this->primaryPhoto();
        return $primaryPhoto?->photo_path;
    }
}