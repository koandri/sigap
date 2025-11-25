<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkOrder extends Model
{
    use HasFiles;
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
        'assigned_by',
        'assigned_at',
        'work_started_at',
        'work_finished_at',
        'verified_at',
        'verified_by',
        'verification_notes',
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
        'assigned_at' => 'datetime',
        'work_started_at' => 'datetime',
        'work_finished_at' => 'datetime',
        'verified_at' => 'datetime',
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
     * Get the user who assigned this work order.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who verified this work order.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
     * Get all progress logs for this work order.
     */
    public function progressLogs(): HasMany
    {
        return $this->hasMany(WorkOrderProgressLog::class);
    }

    /**
     * Get all actions for this work order.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkOrderAction::class);
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
     * Scope to get submitted work orders.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to get assigned work orders.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope to get work orders pending verification.
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending-verification');
    }

    /**
     * Scope to get verified work orders.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope to get open work orders.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['submitted', 'assigned', 'in-progress', 'pending-verification']);
    }

    /**
     * Scope to get completed work orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter work orders accessible by user.
     */
    public function scopeAccessibleBy($query, $user)
    {
        // Super Admin and Owner can see all
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return $query;
        }

        // Engineering Staff can see all work orders
        if ($user->hasRole('Engineering')) {
            return $query;
        }

        // Engineering Operator can only see work orders assigned to them
        if ($user->hasRole('Engineering Operator')) {
            return $query->where('assigned_to', $user->id);
        }

        // Other users can see:
        // 1. WOs created by themselves
        // 2. WOs created by their staff (if user is a manager)
        return $query->where(function ($q) use ($user) {
            $q->where('requested_by', $user->id)
              ->orWhereHas('requestedBy', function ($subQ) use ($user) {
                  $subQ->where('manager_id', $user->id);
              });
        });
    }
}