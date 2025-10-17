<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CleaningScheduleAlert extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cleaning_schedule_id',
        'cleaning_schedule_item_id',
        'asset_id',
        'alert_type',
        'detected_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the cleaning schedule this alert belongs to.
     */
    public function cleaningSchedule(): BelongsTo
    {
        return $this->belongsTo(CleaningSchedule::class);
    }

    /**
     * Get the cleaning schedule item this alert is for.
     */
    public function cleaningScheduleItem(): BelongsTo
    {
        return $this->belongsTo(CleaningScheduleItem::class);
    }

    /**
     * Get the asset that triggered this alert.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who resolved this alert.
     */
    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope to filter unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope to filter by alert type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Check if this alert is resolved.
     */
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
