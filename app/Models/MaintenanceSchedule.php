<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MaintenanceSchedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_id',
        'maintenance_type_id',
        'frequency_days',
        'last_performed_at',
        'next_due_date',
        'description',
        'checklist',
        'assigned_to',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_performed_at' => 'datetime',
        'next_due_date' => 'datetime',
        'checklist' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the asset that owns the schedule.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the maintenance type for this schedule.
     */
    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    /**
     * Get the user assigned to this schedule.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get overdue schedules.
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now());
    }

    /**
     * Scope to get upcoming schedules.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('next_due_date', [now(), now()->addDays($days)]);
    }
}