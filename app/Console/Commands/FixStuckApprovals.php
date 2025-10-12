<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use App\Services\ApprovalService;
use Illuminate\Console\Command;

class FixStuckApprovals extends Command
{
    protected $signature = 'approvals:fix {submission_code?}';
    protected $description = 'Fix stuck approval workflows';

    public function handle()
    {
        $approvalService = app(ApprovalService::class);
        $submissionCode = $this->argument('submission_code');
        
        if ($submissionCode) {
            // Fix specific submission
            $submission = FormSubmission::where('submission_code', $submissionCode)->first();
            
            if (!$submission) {
                $this->error("Submission {$submissionCode} not found");
                return;
            }
            
            $this->fixSubmission($submission, $approvalService);
        } else {
            // Fix all stuck submissions
            $stuckSubmissions = FormSubmission::where('status', 'under_review')
                ->whereHas('approvalLogs', function($query) {
                    $query->where('status', 'approved');
                })
                ->get();
                
            $this->info("Found {$stuckSubmissions->count()} potentially stuck submission(s)");
            
            foreach ($stuckSubmissions as $submission) {
                $this->fixSubmission($submission, $approvalService);
            }
        }
    }
    
    private function fixSubmission($submission, $approvalService)
    {
        $this->info("Checking submission: {$submission->submission_code}");
        
        // Force recheck workflow progress
        $reflection = new \ReflectionClass($approvalService);
        $method = $reflection->getMethod('checkWorkflowProgress');
        $method->setAccessible(true);
        $method->invoke($approvalService, $submission);
        
        // Refresh submission from database
        $submission->refresh();
        
        $this->info("Submission {$submission->submission_code} status: {$submission->status}");
    }
}