<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalFlowStep;
use App\Models\ApprovalLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    /**
     * Start approval workflow for a submission
     */
    public function startApprovalWorkflow(FormSubmission $submission): bool
    {
        Log::info("Starting approval workflow for submission {$submission->submission_code}");
        
        // Don't start workflow for draft submissions
        if ($submission->status === FormSubmission::STATUS_DRAFT) {
            Log::info("Submission is draft, skipping workflow");
            return true;
        }

        $form = $submission->formVersion->form;
        Log::info("Form requires approval: " . ($form->requires_approval ? 'true' : 'false'));
        
        // Check if form requires approval
        if (!$form->requires_approval) {
            // Auto-approve if no approval required
            Log::info("Form does not require approval, auto-approving");
            $this->autoApprove($submission);
            return true;
        }
        
        // Get active approval workflow
        $workflow = $form->activeApprovalWorkflow;
        Log::info("Active workflow found: " . ($workflow ? 'yes' : 'no'));
        
        if (!$workflow) {
            // Auto-approve if no workflow defined
            Log::info("No active workflow found, auto-approving");
            $this->autoApprove($submission);
            return true;
        }
        
        try {
            DB::beginTransaction();
            
            Log::info("Creating approval logs for workflow type: {$workflow->flow_type}");
            
            // Create approval logs based on workflow type
            if ($workflow->flow_type === ApprovalWorkflow::FLOW_SEQUENTIAL) {
                $this->createSequentialApprovals($submission, $workflow);
            } else {
                $this->createParallelApprovals($submission, $workflow);
            }
            
            // Update submission status
            $submission->update(['status' => FormSubmission::STATUS_UNDER_REVIEW]);
            Log::info("Updated submission status to UNDER_REVIEW");
            
            DB::commit();
            
            // Send notifications to first approvers
            $this->notifyPendingApprovers($submission);
            Log::info("Sent notifications to pending approvers");
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approval workflow failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return false;
        }
    }
    
    /**
     * Create sequential approval logs
     */
    private function createSequentialApprovals(FormSubmission $submission, ApprovalWorkflow $workflow)
    {
        $steps = $workflow->getOrderedSteps();
        $now = now();
        
        foreach ($steps as $index => $step) {
            Log::info("Processing step {$step->id}: {$step->step_name} (type: {$step->approver_type})");
            $approvers = $step->getApprovers();
            Log::info("Found " . $approvers->count() . " approvers for step {$step->id}");
            
            foreach ($approvers as $approver) {
                $dueAt = $step->sla_hours ? $now->copy()->addHours($step->sla_hours) : null;
                
                ApprovalLog::create([
                    'form_submission_id' => $submission->id,
                    'approval_flow_step_id' => $step->id,
                    'assigned_to' => $approver->id,
                    'status' => $index === 0 ? ApprovalLog::STATUS_PENDING : ApprovalLog::STATUS_SKIPPED, // First step pending, others skipped until activated
                    'assigned_at' => $index === 0 ? $now : null,
                    'due_at' => $index === 0 ? $dueAt : null
                ]);
            }
        }
    }
    
    /**
     * Create parallel approval logs (all at once)
     */
    private function createParallelApprovals(FormSubmission $submission, ApprovalWorkflow $workflow)
    {
        $steps = $workflow->getOrderedSteps();
        $now = now();
        
        foreach ($steps as $step) {
            Log::info("Processing step {$step->id}: {$step->step_name} (type: {$step->approver_type})");
            $approvers = $step->getApprovers();
            Log::info("Found " . $approvers->count() . " approvers for step {$step->id}");
            
            foreach ($approvers as $approver) {
                $dueAt = $step->sla_hours ? $now->copy()->addHours($step->sla_hours) : null;
                
                ApprovalLog::create([
                    'form_submission_id' => $submission->id,
                    'approval_flow_step_id' => $step->id,
                    'assigned_to' => $approver->id,
                    'status' => ApprovalLog::STATUS_PENDING, // All steps are pending
                    'assigned_at' => $now,
                    'due_at' => $dueAt
                ]);
            }
        }
    }
    
    /**
     * Process approval action
     */
    public function processApproval(ApprovalLog $approvalLog, User $approver, string $action, string $comments = null): bool
    {
        if (!$approvalLog->isPending()) {
            throw new \Exception('This approval has already been processed');
        }
        
        if ($approvalLog->assigned_to != $approver->id) {
            throw new \Exception('You are not authorized to approve this submission');
        }
        
        if (!in_array($action, [ApprovalLog::STATUS_APPROVED, ApprovalLog::STATUS_REJECTED])) {
            throw new \Exception('Invalid approval action');
        }
        
        try {
            DB::beginTransaction();
            
            // Update approval log
            $approvalLog->update([
                'approved_by' => $approver->id,
                'status' => $action,
                'comments' => $comments,
                'action_at' => now(),
                'metadata' => [
                    'approver_name' => $approver->name,
                    'approver_roles' => $approver->roles->pluck('name')->toArray(),
                    'action_ip' => request()->ip(),
                    'action_user_agent' => request()->userAgent()
                ]
            ]);
            
            $submission = $approvalLog->submission;
            
            // Check if approval should proceed or stop
            if ($action === ApprovalLog::STATUS_REJECTED) {
                // Rejection stops the workflow
                $this->rejectSubmission($submission, $approvalLog);
            } else {
                // Check if we can proceed to next step
                $this->checkWorkflowProgress($submission);
            }
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
    
    /**
     * Check workflow progress and route to next step
     */
    private function checkWorkflowProgress(FormSubmission $submission)
    {
        $workflow = $submission->formVersion->form->activeApprovalWorkflow;
        
        if (!$workflow) {
            return;
        }
    
        if ($workflow->flow_type === ApprovalWorkflow::FLOW_SEQUENTIAL) {
            $this->processSequentialWorkflow($submission, $workflow);
        } else {
            $this->processParallelWorkflow($submission, $workflow);
        }
    }
    
    /**
     * Process sequential workflow progress
     */
    private function processSequentialWorkflow(FormSubmission $submission, ApprovalWorkflow $workflow)
    {
        // Get all approval logs for this submission
        $allApprovalLogs = $submission->approvalLogs()->with('step')->get();
        
        // Group by step
        $stepGroups = $allApprovalLogs->groupBy('approval_flow_step_id');
        
        // Find current step (first step that has pending approvals)
        $currentStep = null;
        
        foreach ($workflow->getOrderedSteps() as $step) {
            $stepApprovals = $stepGroups->get($step->id, collect());
            $pendingCount = $stepApprovals->where('status', ApprovalLog::STATUS_PENDING)->count();
            $approvedCount = $stepApprovals->where('status', ApprovalLog::STATUS_APPROVED)->count();
            
            if ($pendingCount > 0) {
                // This step still has pending approvals
                $currentStep = $step;
                
                break;
            } elseif ($approvedCount === 0 && $stepApprovals->where('status', ApprovalLog::STATUS_SKIPPED)->count() > 0) {
                // This step is skipped and waiting to be activated
                $currentStep = $step;
                
                break;
            }
        }
        
        if (!$currentStep) {
            // All steps completed, check if workflow should complete
            $totalSteps = $workflow->steps->count();
            $completedSteps = 0;
            
            foreach ($workflow->getOrderedSteps() as $step) {
                $stepApprovals = $stepGroups->get($step->id, collect());
                $approvedCount = $stepApprovals->where('status', ApprovalLog::STATUS_APPROVED)->count();
                
                if ($approvedCount > 0) {
                    $completedSteps++;
                }
            }
            
            if ($completedSteps === $totalSteps) {
                $this->completeApprovalWorkflow($submission);
            }
            
            return;
        }
        
        // Check if current step is complete
        $currentStepApprovals = $stepGroups->get($currentStep->id, collect());
        $pendingCurrentStep = $currentStepApprovals->where('status', ApprovalLog::STATUS_PENDING);
        $approvedCurrentStep = $currentStepApprovals->where('status', ApprovalLog::STATUS_APPROVED);
        
        if ($pendingCurrentStep->count() === 0 && $approvedCurrentStep->count() > 0) {
            // Current step is complete, move to next
            $nextStep = $workflow->getNextStep($currentStep->step_order);
            
            if ($nextStep) {
                $this->activateStep($submission, $nextStep);
            } else {
                $this->completeApprovalWorkflow($submission);
            }
        }
    }
    
    /**
     * Process parallel workflow progress
     */
    private function processParallelWorkflow(FormSubmission $submission, ApprovalWorkflow $workflow)
    {
        // Check if all steps are complete
        $allLogs = $submission->approvalLogs;
        $pendingLogs = $allLogs->where('status', ApprovalLog::STATUS_PENDING);
        $rejectedLogs = $allLogs->where('status', ApprovalLog::STATUS_REJECTED);
        $approvedLogs = $allLogs->where('status', ApprovalLog::STATUS_APPROVED);
        
        if ($pendingLogs->count() === 0) {
            // No pending approvals
            if ($rejectedLogs->count() > 0) {
                // At least one rejection
                $this->rejectSubmission($submission, $rejectedLogs->first());
            } else {
                // All approved
                $this->completeApprovalWorkflow($submission);
            }
        }
    }
    
    /**
     * Activate next step in sequential workflow
     */
    private function activateStep(FormSubmission $submission, ApprovalFlowStep $step)
    {
        $now = now();
        $dueAt = $step->sla_hours ? $now->copy()->addHours($step->sla_hours) : null;
        
        // Find and activate skipped approvals for this step
        $submission->approvalLogs()
                   ->where('approval_flow_step_id', $step->id)
                   ->where('status', ApprovalLog::STATUS_SKIPPED)
                   ->update([
                       'status' => ApprovalLog::STATUS_PENDING,
                       'assigned_at' => $now,
                       'due_at' => $dueAt
                   ]);
        
        // Send notifications
        $this->notifyStepApprovers($submission, $step);
    }
    
    /**
     * Complete approval workflow
     */
    private function completeApprovalWorkflow(FormSubmission $submission)
    {        
        $submission->update([
            'status' => FormSubmission::STATUS_APPROVED,
            'completed_at' => now()
        ]);
        
        // Send completion notification
        $this->notifyWorkflowCompletion($submission);
    }
    
    /**
     * Reject submission
     */
    private function rejectSubmission(FormSubmission $submission, ApprovalLog $rejectionLog)
    {
        $submission->update([
            'status' => FormSubmission::STATUS_REJECTED,
            'completed_at' => now()
        ]);
        
        // Cancel all pending approvals
        $submission->approvalLogs()
                   ->where('status', ApprovalLog::STATUS_PENDING)
                   ->where('id', '!=', $rejectionLog->id)
                   ->update(['status' => 'cancelled']);
        
        // Send rejection notification
        $this->notifyWorkflowRejection($submission, $rejectionLog);
    }
    
    /**
     * Auto-approve submission (no workflow required)
     */
    private function autoApprove(FormSubmission $submission)
    {
        $submission->update([
            'status' => FormSubmission::STATUS_APPROVED,
            'completed_at' => now()
        ]);
    }
    
    /**
     * Get pending approvals for user
     */
    public function getPendingApprovalsForUser(User $user, $limit = null)
    {
        $query = ApprovalLog::where('assigned_to', $user->id)
                           ->where('status', ApprovalLog::STATUS_PENDING)
                           ->with(['submission.formVersion.form', 'step'])
                           ->orderBy('due_at', 'asc')
                           ->orderBy('assigned_at', 'asc');
        
        if ($limit) {
            return $query->limit($limit)->get();
        }
        
        return $query->get();
    }
    
    /**
     * Get overdue approvals
     */
    public function getOverdueApprovals($limit = null)
    {
        $query = ApprovalLog::overdue()
                           ->with(['submission.formVersion.form', 'assignedUser', 'step'])
                           ->orderBy('due_at', 'asc');
        
        if ($limit) {
            return $query->limit($limit)->get();
        }
        
        return $query->get();
    }
    
    /**
     * Escalate overdue approvals
     */
    public function escalateOverdueApprovals()
    {
        $overdueApprovals = $this->getOverdueApprovals();
        
        foreach ($overdueApprovals as $approval) {
            try {
                $this->escalateApproval($approval);
            } catch (\Exception $e) {
                Log::error("Failed to escalate approval {$approval->id}: " . $e->getMessage());
            }
        }
        
        return $overdueApprovals->count();
    }
    
    /**
     * Escalate single approval
     */
    private function escalateApproval(ApprovalLog $approval)
    {
        // Find escalation target (e.g., manager of assigned user)
        $assignedUser = $approval->assignedUser;
        $escalationTargets = $this->findEscalationTargets($assignedUser);
        
        if ($escalationTargets->isEmpty()) {
            return;
        }
        
        // Create escalated approval
        foreach ($escalationTargets as $target) {
            ApprovalLog::create([
                'form_submission_id' => $approval->form_submission_id,
                'approval_flow_step_id' => $approval->approval_flow_step_id,
                'assigned_to' => $target->id,
                'status' => ApprovalLog::STATUS_PENDING,
                'assigned_at' => now(),
                'due_at' => now()->addHours(24), // 24 hours for escalation
                'metadata' => [
                    'escalated_from' => $approval->id,
                    'original_assignee' => $assignedUser->name,
                    'escalated_at' => now()->toISOString()
                ]
            ]);
        }
        
        // Mark original as escalated
        $approval->update(['status' => ApprovalLog::STATUS_ESCALATED]);
        
        // Send escalation notifications
        $this->notifyEscalation($approval, $escalationTargets);
    }
    
    /**
     * Find escalation targets for user
     */
    private function findEscalationTargets(User $user)
    {
        // Strategy 1: Find managers in same departments
        $managers = collect();
        
        foreach ($user->departments as $department) {
            $deptManagers = $department->getUsersByRole('Manager');
            $managers = $managers->merge($deptManagers);
        }
        
        // Strategy 2: If no managers, find supervisors
        if ($managers->isEmpty()) {
            foreach ($user->departments as $department) {
                $supervisors = $department->getUsersByRole('Supervisor');
                $managers = $managers->merge($supervisors);
            }
        }
        
        // Strategy 3: If still empty, find business owners
        if ($managers->isEmpty()) {
            $managers = User::where('active', 1)->whereHas('roles', function($query) {
                $query->whereIn('name', array('Super Admin', 'Owner'));
            })->get();
        }
        
        return $managers->unique('id');
    }
    
    /**
     * Check if user can approve submission
     */
    public function canUserApprove(FormSubmission $submission, User $user): bool
    {
        return $submission->approvalLogs()
                         ->where('assigned_to', $user->id)
                         ->where('status', ApprovalLog::STATUS_PENDING)
                         ->exists();
    }
    
    /**
     * Get approval summary for submission
     */
    public function getApprovalSummary(FormSubmission $submission): array
    {
        $logs = $submission->approvalHistory;
        
        $summary = [
            'total_steps' => 0,
            'completed_steps' => 0,
            'pending_steps' => 0,
            'rejected_steps' => 0,
            'overdue_steps' => 0,
            'current_step' => null,
            'next_step' => null,
            'progress_percentage' => 0
        ];
        
        if ($logs->isEmpty()) {
            return $summary;
        }
        
        // Group by step
        $stepGroups = $logs->groupBy('approval_flow_step_id');
        
        $summary['total_steps'] = $stepGroups->count();
        
        foreach ($stepGroups as $stepId => $stepLogs) {
            $approvedCount = $stepLogs->where('status', ApprovalLog::STATUS_APPROVED)->count();
            $rejectedCount = $stepLogs->where('status', ApprovalLog::STATUS_REJECTED)->count();
            $pendingCount = $stepLogs->where('status', ApprovalLog::STATUS_PENDING)->count();
            $overdueCount = $stepLogs->filter(function($log) {
                return $log->isOverdue();
            })->count();
            
            if ($approvedCount > 0) {
                $summary['completed_steps']++;
            } elseif ($rejectedCount > 0) {
                $summary['rejected_steps']++;
            } elseif ($pendingCount > 0) {
                $summary['pending_steps']++;
                if (!$summary['current_step']) {
                    $summary['current_step'] = $stepLogs->first()->step;
                }
            }
            
            $summary['overdue_steps'] += $overdueCount;
        }
        
        $summary['progress_percentage'] = $summary['total_steps'] > 0 
            ? round(($summary['completed_steps'] / $summary['total_steps']) * 100) 
            : 0;
        
        return $summary;
    }
    
    /**
     * Notification methods (placeholders)
     */
    private function notifyPendingApprovers(FormSubmission $submission)
    {
        $pendingApprovals = $submission->getPendingApprovals();
        
        foreach ($pendingApprovals as $approval) {
            // TODO: Send email/notification
            // Mail::to($approval->assignedUser->email)->send(new ApprovalRequestNotification($approval));
        }
    }
    
    private function notifyStepApprovers(FormSubmission $submission, ApprovalFlowStep $step)
    {
        $approvers = $step->getApprovers();
        
        foreach ($approvers as $approver) {
            // TODO: Send notification
        }
    }
    
    private function notifyWorkflowCompletion(FormSubmission $submission)
    {
        // Notify submitter
        $submitter = $submission->submitter;
        // TODO: Send completion notification
    }
    
    private function notifyWorkflowRejection(FormSubmission $submission, ApprovalLog $rejectionLog)
    {
        // Notify submitter
        $submitter = $submission->submitter;
        // TODO: Send rejection notification
    }
    
    private function notifyEscalation(ApprovalLog $originalApproval, $escalationTargets)
    {
        foreach ($escalationTargets as $target) {
            // TODO: Send escalation notification
        }
    }
    
    /**
     * Get workflow statistics
     */
    public function getWorkflowStatistics($dateFrom = null, $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: now()->startOfMonth();
        $dateTo = $dateTo ?: now()->endOfMonth();
        
        $submissions = FormSubmission::whereBetween('created_at', [$dateFrom, $dateTo])
                                    ->whereHas('formVersion.form', function($query) {
                                        $query->where('requires_approval', true);
                                    });
        
        return [
            'total_submissions' => $submissions->count(),
            'approved' => $submissions->where('status', FormSubmission::STATUS_APPROVED)->count(),
            'rejected' => $submissions->where('status', FormSubmission::STATUS_REJECTED)->count(),
            'pending' => $submissions->where('status', FormSubmission::STATUS_UNDER_REVIEW)->count(),
            'average_approval_time' => $this->getAverageApprovalTime($dateFrom, $dateTo),
            'overdue_count' => ApprovalLog::overdue()->count()
        ];
    }
    
    /**
     * Get average approval time
     */
    private function getAverageApprovalTime($dateFrom, $dateTo): float
    {
        $completedSubmissions = FormSubmission::whereBetween('created_at', [$dateFrom, $dateTo])
                                             ->whereIn('status', [FormSubmission::STATUS_APPROVED, FormSubmission::STATUS_REJECTED])
                                             ->whereNotNull('completed_at')
                                             ->get();
        
        if ($completedSubmissions->isEmpty()) {
            return 0;
        }
        
        $totalHours = $completedSubmissions->sum(function($submission) {
            return $submission->submitted_at->diffInHours($submission->completed_at);
        });
        
        return round($totalHours / $completedSubmissions->count(), 2);
    }
}