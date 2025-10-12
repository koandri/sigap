<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'approval_flow_step_id',
        'assigned_to',
        'approved_by',
        'status',
        'comments',
        'metadata',
        'assigned_at',
        'action_at',
        'due_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'assigned_at' => 'datetime',
        'action_at' => 'datetime',
        'due_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_ESCALATED = 'escalated';

    // Relationships
    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlowStep::class, 'approval_flow_step_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isOverdue(): bool
    {
        return $this->due_at && now()->greaterThan($this->due_at) && $this->isPending();
    }

    public function getTimeRemaining(): ?string
    {
        if (!$this->due_at || !$this->isPending()) {
            return null;
        }
        
        $diff = $this->due_at->diffInHours(now(), false);
        
        if ($diff < 0) {
            return 'Overdue by ' . abs($diff) . ' hours';
        } else {
            return $diff . ' hours remaining';
        }
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('due_at', '<', now());
    }
}