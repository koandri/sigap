<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_code',
        'form_version_id',
        'submitted_by',
        'status',
        'metadata',
        'submitted_at',
        'completed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    public function needsApproval(): bool
    {
        return $this->formVersion->form->requires_approval;
    }

    public function isAwaitingApproval(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function getPendingApprovals()
    {
        return $this->approvalLogs()
                    ->where('status', ApprovalLog::STATUS_PENDING)
                    ->get();
    }

    public function canBeApprovedBy(User $user): bool
    {
        $pendingApprovals = $this->getPendingApprovals();
        
        foreach ($pendingApprovals as $approval) {
            if ($approval->assigned_to == $user->id) {
                return true;
            }
        }
        
        return false;
    }

    public function getApprovalProgress(): array
    {
        $logs = $this->approvalHistory;
        $total = $logs->count();
        $completed = $logs->whereIn('status', [ApprovalLog::STATUS_APPROVED, ApprovalLog::STATUS_REJECTED])->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }

    public function getAnswer($fieldCode)
    {
        return $this->answers()
            ->whereHas('field', function($query) use ($fieldCode) {
                $query->where('field_code', $fieldCode);
            })
            ->first()?->answer_value;
    }

    public function generateSubmissionCode(): string
    {
        $form = $this->formVersion->form;
        $prefix = strtoupper(substr($form->form_no, 0, 3));
        $yearMonth = date('Ym');
        
        $lastCode = self::where('submission_code', 'like', "$prefix-$yearMonth-%")
            ->orderBy('submission_code', 'desc')
            ->first()?->submission_code;

        $sequence = 1;
        if ($lastCode) {
            $sequence = intval(substr($lastCode, -4)) + 1;
        }

        return sprintf("%s-%s-%04d", $prefix, $yearMonth, $sequence);
    }

    public function submit()
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now()
        ]);
    }

    /**
     * Get submissions from user's departments
     */
    public function scopeFromUserDepartments($query, $userId)
    {
        $user = User::find($userId);
        $departmentIds = $user->departments->pluck('id');
        
        return $query->whereHas('submitter', function($q) use ($departmentIds) {
            $q->whereHas('departments', function($q2) use ($departmentIds) {
                $q2->whereIn('departments.id', $departmentIds);
            });
        });
    }

    /**
     * Check if user can view this submission (same department)
     */
    public function canBeViewedBy(User $user)
    {
        // Get submitter's departments
        $submitterDeptIds = $this->submitter->departments->pluck('id');
        
        // Get viewer's departments
        $viewerDeptIds = $user->departments->pluck('id');
        
        // Check if there's any intersection
        return $submitterDeptIds->intersect($viewerDeptIds)->isNotEmpty();
    }

    // Relationships
    public function formVersion(): BelongsTo
    {
        return $this->belongsTo(FormVersion::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FormAnswer::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function currentApprovalStep()
    {
        return $this->hasOne(ApprovalLog::class)
                    ->where('status', ApprovalLog::STATUS_PENDING)
                    ->with('step');
    }

    public function approvalHistory()
    {
        return $this->hasMany(ApprovalLog::class)
                    ->with(['step', 'approver'])
                    ->orderBy('created_at', 'asc');
    }

    
}