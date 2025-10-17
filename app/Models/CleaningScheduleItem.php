<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CleaningScheduleItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cleaning_schedule_id',
        'asset_id',
        'item_name',
        'item_description',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the cleaning schedule this item belongs to.
     */
    public function cleaningSchedule(): BelongsTo
    {
        return $this->belongsTo(CleaningSchedule::class);
    }

    /**
     * Get the asset associated with this item (if any).
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get all tasks generated from this schedule item.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CleaningTask::class);
    }

    /**
     * Get all alerts for this schedule item.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(CleaningScheduleAlert::class);
    }

    /**
     * Check if this item has an asset reference.
     */
    public function hasAsset(): bool
    {
        return $this->asset_id !== null;
    }

    /**
     * Check if the associated asset is active.
     */
    public function isAssetActive(): bool
    {
        if (!$this->hasAsset()) {
            return true; // Non-asset items are always "active"
        }

        return $this->asset?->is_active ?? false;
    }
}
