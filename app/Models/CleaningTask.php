<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CleaningTask extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'task_number',
        'cleaning_schedule_id',
        'cleaning_schedule_item_id',
        'location_id',
        'asset_id',
        'item_name',
        'item_description',
        'scheduled_date',
        'assigned_to',
        'started_by',
        'started_at',
        'status',
        'completed_at',
        'completed_by',
        'skip_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the cleaning schedule this task belongs to.
     */
    public function cleaningSchedule(): BelongsTo
    {
        return $this->belongsTo(CleaningSchedule::class);
    }

    /**
     * Get the cleaning schedule item this task was generated from.
     */
    public function cleaningScheduleItem(): BelongsTo
    {
        return $this->belongsTo(CleaningScheduleItem::class);
    }

    /**
     * Get the location for this task.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the asset for this task (if any).
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who started this task.
     */
    public function startedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * Get the user who completed this task.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the submission for this task.
     */
    public function submission(): HasOne
    {
        return $this->hasOne(CleaningSubmission::class);
    }

    /**
     * Scope to filter tasks for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    /**
     * Scope to filter tasks for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter approved tasks.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter tasks assigned to a user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Check if task is locked (started by someone).
     */
    public function isLocked(): bool
    {
        return $this->started_by !== null && $this->status === 'in-progress';
    }

    /**
     * Check if task can be started by a user.
     */
    public function canBeStartedBy(int $userId): bool
    {
        // Can't start if already locked by someone else
        if ($this->isLocked() && $this->started_by !== $userId) {
            return false;
        }

        // Can't start if not today's task
        if (!$this->scheduled_date->isToday()) {
            return false;
        }

        // Can't start if already completed/approved/rejected
        if (in_array($this->status, ['completed', 'approved', 'rejected', 'missed'])) {
            return false;
        }

        return true;
    }
}
