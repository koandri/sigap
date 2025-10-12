<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalFlowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_workflow_id',
        'step_order',
        'step_name',
        'approver_type',
        'approver_user_id',
        'approver_role',
        'approver_department_id',
        'sla_hours',
        'is_required',
        'conditions'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'conditions' => 'array'
    ];

    // Approver types
    const TYPE_USER = 'user';
    const TYPE_ROLE = 'role';
    const TYPE_DEPARTMENT = 'department';

    // Relationships
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function approverDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'approver_department_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    // Helper methods
    public function getApprovers()
    {
        try {
            switch ($this->approver_type) {
                case self::TYPE_USER:
                    return collect([$this->approverUser])->filter();
                    
                case self::TYPE_ROLE:
                    if (!$this->approver_role) {
                        return collect();
                    }
                    return User::whereHas('roles', function($query) {
                        $query->where('name', $this->approver_role);
                    })->get();
                    
                case self::TYPE_DEPARTMENT:
                    if (!$this->approverDepartment) {
                        return collect();
                    }
                    return $this->approverDepartment->users ?? collect();
                    
                default:
                    return collect();
            }
        } catch (\Exception $e) {
            \Log::error("Error getting approvers for step {$this->id}: " . $e->getMessage());
            return collect();
        }
    }

    public function getStepPosition(): int
    {
        return $this->workflow->getOrderedSteps()->search(function ($step) {
            return $step->id === $this->id;
        }) + 1;
    }

    public function getApproverDisplayName(): string
    {
        switch ($this->approver_type) {
            case self::TYPE_USER:
                return $this->approverUser?->name ?? 'Unknown User';
                
            case self::TYPE_ROLE:
                $role = Role::where('name', $this->approver_role)->first();
                return $role?->name ?? $this->approver_role;
                
            case self::TYPE_DEPARTMENT:
                return $this->approverDepartment?->name ?? 'Unknown Department';
                
            default:
                return 'Unknown';
        }
    }

    public function getDueDate($fromDate = null): ?\Carbon\Carbon
    {
        if (!$this->sla_hours) {
            return null;
        }
        
        $startDate = $fromDate ? \Carbon\Carbon::parse($fromDate) : now();
        return $startDate->addHours($this->sla_hours);
    }

    public function isOverdue($fromDate = null): bool
    {
        $dueDate = $this->getDueDate($fromDate);
        return $dueDate && now()->greaterThan($dueDate);
    }
}