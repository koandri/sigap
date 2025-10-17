<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\CleaningSchedule;
use App\Models\CleaningScheduleAlert;
use App\Models\CleaningScheduleItem;
use App\Models\CleaningTask;
use App\Models\CleaningSubmission;
use App\Models\CleaningApproval;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class CleaningService
{
    /**
     * Generate daily cleaning tasks from schedules.
     */
    public function generateDailyTasks(Carbon $date = null): int
    {
        $date = $date ?? today();
        $generatedCount = 0;

        $schedules = CleaningSchedule::active()
            ->with(['items.asset', 'location'])
            ->get();

        foreach ($schedules as $schedule) {
            if ($this->shouldGenerateForDate($schedule, $date)) {
                foreach ($schedule->items as $item) {
                    $generatedCount += $this->generateTaskForItem($schedule, $item, $date);
                }
            }
        }

        return $generatedCount;
    }

    /**
     * Check if schedule should generate tasks for given date.
     */
    private function shouldGenerateForDate(CleaningSchedule $schedule, Carbon $date): bool
    {
        $config = $schedule->frequency_config ?? [];

        return match($schedule->frequency_type) {
            'daily' => $this->shouldGenerateDaily($date, $config),
            'weekly' => $this->shouldGenerateWeekly($date, $config),
            'monthly' => $this->shouldGenerateMonthly($date, $config),
            default => false,
        };
    }

    private function shouldGenerateDaily(Carbon $date, array $config): bool
    {
        $interval = $config['interval'] ?? 1;
        
        if ($interval == 1) {
            return true; // Every day
        }

        // Check if date matches interval
        $dayOfYear = $date->dayOfYear;
        return ($dayOfYear % $interval) == 0;
    }

    private function shouldGenerateWeekly(Carbon $date, array $config): bool
    {
        $days = $config['days'] ?? [];
        
        if (empty($days)) {
            return false;
        }

        // Check if current day of week is in configured days
        $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday
        return in_array($dayOfWeek, $days);
    }

    private function shouldGenerateMonthly(Carbon $date, array $config): bool
    {
        $dates = $config['dates'] ?? [];
        
        if (empty($dates)) {
            return false;
        }

        // Check if current day of month is in configured dates
        return in_array($date->day, $dates);
    }

    /**
     * Generate a cleaning task for a schedule item.
     */
    private function generateTaskForItem(
        CleaningSchedule $schedule, 
        CleaningScheduleItem $item, 
        Carbon $date
    ): int {
        // Check if task already exists for this date
        $exists = CleaningTask::where('cleaning_schedule_id', $schedule->id)
            ->where('cleaning_schedule_item_id', $item->id)
            ->whereDate('scheduled_date', $date)
            ->exists();

        if ($exists) {
            return 0; // Task already generated
        }

        // Check if asset is active (if item has asset)
        if ($item->hasAsset()) {
            $asset = $item->asset;
            
            if (!$asset || !$asset->is_active) {
                // Asset is inactive or disposed - create alert and skip task
                $this->createScheduleAlert($schedule, $item, $asset);
                
                Log::info("Skipped task generation for inactive asset", [
                    'schedule_id' => $schedule->id,
                    'item_id' => $item->id,
                    'asset_id' => $item->asset_id,
                    'date' => $date->toDateString(),
                ]);
                
                return 0;
            }
        }

        // Determine assigned cleaner (rotate based on date or use default)
        $assignedTo = $this->determineAssignedCleaner($schedule, $date);

        if (!$assignedTo) {
            Log::warning("No cleaner available for assignment", [
                'schedule_id' => $schedule->id,
                'date' => $date->toDateString(),
            ]);
            return 0;
        }

        // Create task
        CleaningTask::create([
            'task_number' => $this->generateTaskNumber($date),
            'cleaning_schedule_id' => $schedule->id,
            'cleaning_schedule_item_id' => $item->id,
            'location_id' => $schedule->location_id,
            'asset_id' => $item->asset_id,
            'item_name' => $item->item_name,
            'item_description' => $item->item_description,
            'scheduled_date' => $date,
            'assigned_to' => $assignedTo,
            'status' => 'pending',
        ]);

        return 1;
    }

    /**
     * Determine which cleaner should be assigned to this task.
     */
    private function determineAssignedCleaner(CleaningSchedule $schedule, Carbon $date): ?int
    {
        // Get all users with Cleaner role
        $cleaners = \App\Models\User::role('Cleaner')->pluck('id')->toArray();

        if (empty($cleaners)) {
            return null;
        }

        // Simple round-robin based on date
        $index = ($date->dayOfYear + $schedule->id) % count($cleaners);
        return $cleaners[$index];
    }

    /**
     * Generate unique task number.
     */
    private function generateTaskNumber(Carbon $date): string
    {
        $prefix = 'CT-' . $date->format('ymd');
        $lastTask = CleaningTask::where('task_number', 'like', $prefix . '%')
            ->orderBy('task_number', 'desc')
            ->first();

        if ($lastTask) {
            $lastNumber = (int) substr($lastTask->task_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create alert for problematic asset.
     */
    public function createScheduleAlert(
        CleaningSchedule $schedule,
        CleaningScheduleItem $item,
        ?Asset $asset
    ): void {
        // Check if alert already exists and is unresolved
        $existingAlert = CleaningScheduleAlert::where('cleaning_schedule_id', $schedule->id)
            ->where('cleaning_schedule_item_id', $item->id)
            ->whereNull('resolved_at')
            ->first();

        if ($existingAlert) {
            return; // Alert already exists
        }

        $alertType = $asset && !$asset->is_active ? 'asset_inactive' : 'asset_disposed';

        CleaningScheduleAlert::create([
            'cleaning_schedule_id' => $schedule->id,
            'cleaning_schedule_item_id' => $item->id,
            'asset_id' => $item->asset_id,
            'alert_type' => $alertType,
            'detected_at' => now(),
        ]);

        // TODO: Send notification to General Affairs staff
        Log::info("Created cleaning schedule alert", [
            'schedule_id' => $schedule->id,
            'item_id' => $item->id,
            'alert_type' => $alertType,
        ]);
    }

    /**
     * Resolve an alert.
     */
    public function resolveAlert(CleaningScheduleAlert $alert, int $userId, string $notes): void
    {
        $alert->update([
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Flag random tasks for mandatory review (10-20%).
     */
    public function flagRandomTasksForReview(Carbon $date = null): int
    {
        $date = $date ?? today()->subDay(); // Yesterday's submissions
        
        $submissions = CleaningSubmission::whereDate('submitted_at', $date)
            ->with('approval')
            ->get();

        if ($submissions->isEmpty()) {
            return 0;
        }

        // Flag 15% on average (between 10-20%)
        $flagPercentage = 0.15;
        $flagCount = max((int) ceil($submissions->count() * $flagPercentage), 1);

        // Randomly select submissions to flag
        $toFlag = $submissions->random(min($flagCount, $submissions->count()));

        foreach ($toFlag as $submission) {
            if ($submission->approval) {
                $submission->approval->update([
                    'is_flagged_for_review' => true,
                ]);
            }
        }

        Log::info("Flagged submissions for review", [
            'date' => $date->toDateString(),
            'total_submissions' => $submissions->count(),
            'flagged_count' => $flagCount,
        ]);

        return $flagCount;
    }

    /**
     * Check if batch can be approved (10% of flagged tasks reviewed).
     */
    public function canApproveBatch(Carbon $date = null): array
    {
        $date = $date ?? today()->subDay();

        $flaggedCount = CleaningApproval::whereHas('cleaningSubmission', function($q) use ($date) {
                $q->whereDate('submitted_at', $date);
            })
            ->where('is_flagged_for_review', true)
            ->count();

        $reviewedCount = CleaningApproval::whereHas('cleaningSubmission', function($q) use ($date) {
                $q->whereDate('submitted_at', $date);
            })
            ->where('is_flagged_for_review', true)
            ->whereNotNull('reviewed_at')
            ->count();

        $canApprove = true;
        $message = '';
        $percentage = 0;

        if ($flaggedCount > 0) {
            $percentage = ($reviewedCount / $flaggedCount) * 100;
            
            if ($percentage < 10) {
                $canApprove = false;
                $message = sprintf(
                    'You must review at least 10%% of flagged tasks before mass approval. Currently reviewed: %d of %d (%.1f%%)',
                    $reviewedCount,
                    $flaggedCount,
                    $percentage
                );
            }
        }

        return [
            'can_approve' => $canApprove,
            'message' => $message,
            'flagged_count' => $flaggedCount,
            'reviewed_count' => $reviewedCount,
            'percentage' => $percentage,
        ];
    }

    /**
     * Mark missed tasks (not completed by end of day).
     */
    public function markMissedTasks(Carbon $date = null): int
    {
        $date = $date ?? today()->subDay(); // Yesterday
        
        $missedCount = CleaningTask::whereDate('scheduled_date', $date)
            ->whereIn('status', ['pending', 'in-progress'])
            ->update(['status' => 'missed']);

        Log::info("Marked missed cleaning tasks", [
            'date' => $date->toDateString(),
            'count' => $missedCount,
        ]);

        return $missedCount;
    }

    /**
     * Release locked tasks that have been inactive for more than 2 hours.
     */
    public function releaseInactiveTasks(): int
    {
        $twoHoursAgo = now()->subHours(2);
        
        $releasedCount = CleaningTask::where('status', 'in-progress')
            ->whereNotNull('started_at')
            ->where('started_at', '<', $twoHoursAgo)
            ->whereNull('completed_at')
            ->update([
                'status' => 'pending',
                'started_by' => null,
                'started_at' => null,
            ]);

        if ($releasedCount > 0) {
            Log::info("Released inactive cleaning tasks", [
                'count' => $releasedCount,
            ]);
        }

        return $releasedCount;
    }

    /**
     * Bulk reassign tasks from one user to another.
     */
    public function bulkReassignTasks(int $fromUserId, int $toUserId, ?Carbon $startDate = null): int
    {
        $query = CleaningTask::where('assigned_to', $fromUserId)
            ->whereIn('status', ['pending', 'in-progress']);

        if ($startDate) {
            $query->where('scheduled_date', '>=', $startDate);
        }

        $count = $query->update(['assigned_to' => $toUserId]);

        Log::info("Bulk reassigned cleaning tasks", [
            'from_user' => $fromUserId,
            'to_user' => $toUserId,
            'count' => $count,
        ]);

        return $count;
    }
}

