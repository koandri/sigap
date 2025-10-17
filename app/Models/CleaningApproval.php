<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CleaningApproval extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'cleaning_submission_id',
        'is_flagged_for_review',
        'reviewed_at',
        'approved_by',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_flagged_for_review' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the cleaning submission this approval belongs to.
     */
    public function cleaningSubmission(): BelongsTo
    {
        return $this->belongsTo(CleaningSubmission::class);
    }

    /**
     * Get the user who approved/rejected this.
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the approval deadline (9am the day after submission).
     */
    public function getApprovalDeadlineAttribute(): Carbon
    {
        return $this->cleaningSubmission->submitted_at->addDay()->setTime(9, 0, 0);
    }

    /**
     * Get hours overdue from the deadline.
     */
    public function getHoursOverdueAttribute(): float
    {
        if ($this->status !== 'pending') {
            return 0.0; // Already approved/rejected
        }
        
        $now = now();
        $deadline = $this->approval_deadline;
        
        if ($now->lt($deadline)) {
            return 0.0; // Not yet overdue
        }
        
        return $deadline->diffInHours($now, true);
    }

    /**
     * Get SLA status based on hours overdue.
     */
    public function getSlaStatusAttribute(): string
    {
        $hours = $this->hours_overdue;
        
        if ($hours == 0) {
            return 'on-time'; // green
        }
        if ($hours < 24) {
            return 'warning'; // yellow
        }
        return 'critical'; // red (>24hrs overdue)
    }

    /**
     * Get color class for SLA status.
     */
    public function getSlaColorAttribute(): string
    {
        return match($this->sla_status) {
            'on-time' => 'success',
            'warning' => 'warning',
            'critical' => 'danger',
        };
    }

    /**
     * Scope to filter pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter flagged for review.
     */
    public function scopeFlaggedForReview($query)
    {
        return $query->where('is_flagged_for_review', true);
    }

    /**
     * Scope to filter reviewed items.
     */
    public function scopeReviewed($query)
    {
        return $query->whereNotNull('reviewed_at');
    }
}
