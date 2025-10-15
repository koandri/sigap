<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'wo_number',
        'asset_id',
        'maintenance_type_id',
        'priority',
        'status',
        'scheduled_date',
        'completed_date',
        'assigned_to',
        'requested_by',
        'estimated_hours',
        'actual_hours',
        'description',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    /**
     * Get the asset that owns the work order.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the maintenance type for this work order.
     */
    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    /**
     * Get the user assigned to this work order.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who requested this work order.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get all parts used in this work order.
     */
    public function parts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    /**
     * Get all maintenance logs for this work order.
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Scope to get work orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get work orders by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get open work orders.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['pending', 'in-progress']);
    }

    /**
     * Scope to get completed work orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}