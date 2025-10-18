<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FrequencyType;
use App\Models\Asset;
use App\Models\CleaningSchedule;
use App\Models\CleaningScheduleAlert;
use App\Models\CleaningScheduleItem;
use App\Models\CleaningTask;
use App\Models\CleaningSubmission;
use App\Models\CleaningApproval;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class CleaningService
{
    public function __construct(
        private WhatsAppService $whatsAppService,
        private PushoverService $pushoverService
    ) {
    }
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
                // For hourly schedules, generate multiple tasks per day
                if ($schedule->frequency_type === FrequencyType::HOURLY) {
                    $generatedCount += $this->generateHourlyTasks($schedule, $date);
                } else {
                    // For other frequencies, generate one task per item
                    foreach ($schedule->items as $item) {
                        $generatedCount += $this->generateTaskForItem($schedule, $item, $date);
                    }
                }
            }
        }

        return $generatedCount;
    }

    /**
     * Generate multiple hourly tasks for a schedule.
     */
    private function generateHourlyTasks(CleaningSchedule $schedule, Carbon $date): int
    {
        $config = $schedule->frequency_config ?? [];
        $interval = $config['interval'] ?? 1; // Hours between tasks
        $generatedCount = 0;

        // Get start and end times
        $startTime = $schedule->start_time ? Carbon::parse($schedule->start_time) : Carbon::parse('00:00');
        $endTime = $schedule->end_time ? Carbon::parse($schedule->end_time) : Carbon::parse('23:59');

        $currentTime = $startTime->copy();

        while ($currentTime->lessThanOrEqualTo($endTime)) {
            foreach ($schedule->items as $item) {
                $generatedCount += $this->generateTaskForItem($schedule, $item, $date, $currentTime);
            }
            $currentTime->addHours($interval);
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
            FrequencyType::HOURLY => true, // Always generate for current day
            FrequencyType::DAILY => $this->shouldGenerateDaily($date, $config),
            FrequencyType::WEEKLY => $this->shouldGenerateWeekly($date, $config),
            FrequencyType::MONTHLY => $this->shouldGenerateMonthly($date, $config),
            FrequencyType::YEARLY => $this->shouldGenerateYearly($date, $config),
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

        // Check if current day of week is in configured days (0=Sunday, 1=Monday, etc.)
        $dayOfWeek = $date->dayOfWeek;
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

    private function shouldGenerateYearly(Carbon $date, array $config): bool
    {
        $month = $config['month'] ?? null;
        $day = $config['date'] ?? null;

        if (!$month || !$day) {
            return false;
        }

        return $date->month == $month && $date->day == $day;
    }

    /**
     * Generate a cleaning task for a schedule item.
     */
    private function generateTaskForItem(
        CleaningSchedule $schedule, 
        CleaningScheduleItem $item, 
        Carbon $date,
        ?Carbon $time = null
    ): int {
        // Combine date and time for scheduled_date
        $scheduledDateTime = $date->copy();
        
        if ($time) {
            // For hourly tasks, use the specific time
            $scheduledDateTime->setTimeFrom($time);
        } elseif ($schedule->scheduled_time) {
            // For daily/weekly/monthly with specific time
            $scheduledDateTime->setTimeFrom($schedule->scheduled_time);
        }

        // Check if task already exists for this exact date+time
        $exists = CleaningTask::where('cleaning_schedule_id', $schedule->id)
            ->where('cleaning_schedule_item_id', $item->id)
            ->where('scheduled_date', $scheduledDateTime)
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
                    'time' => $time?->format('H:i'),
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
            'task_number' => $this->generateTaskNumber($scheduledDateTime),
            'cleaning_schedule_id' => $schedule->id,
            'cleaning_schedule_item_id' => $item->id,
            'location_id' => $schedule->location_id,
            'asset_id' => $item->asset_id,
            'item_name' => $item->item_name,
            'item_description' => $item->item_description,
            'scheduled_date' => $scheduledDateTime,
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

        $alert = CleaningScheduleAlert::create([
            'cleaning_schedule_id' => $schedule->id,
            'cleaning_schedule_item_id' => $item->id,
            'asset_id' => $item->asset_id,
            'alert_type' => $alertType,
            'detected_at' => now(),
        ]);

        Log::info("Created cleaning schedule alert", [
            'schedule_id' => $schedule->id,
            'item_id' => $item->id,
            'alert_type' => $alertType,
        ]);

        // Send notification to General Affairs staff
        $this->notifyScheduleAlert($alert);
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

                // Send notification for flagged submission
                $this->notifyFlaggedForReview($submission->approval);
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

        // Send notification about missed tasks
        $this->notifyMissedTasks($missedCount, $date);

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

    /**
     * Send notification to user via WhatsApp, fallback to Pushover on failure.
     */
    private function sendNotificationToUser(User $user, string $message, string $notificationType): bool
    {
        // Check if user has mobile phone number
        if (empty($user->mobilephone_no)) {
            Log::warning("User has no mobile phone number for WhatsApp notification", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'notification_type' => $notificationType,
            ]);
            
            // Send failure notification via Pushover
            $this->pushoverService->sendWhatsAppFailureNotification(
                $notificationType,
                $user->name . ' (No Phone)',
                $message
            );
            
            return false;
        }

        // Format WhatsApp chat ID (phone number + @c.us)
        $chatId = $this->formatWhatsAppChatId($user->mobilephone_no);

        // Try to send via WhatsApp
        $whatsAppSuccess = $this->whatsAppService->sendMessage($chatId, $message);

        if (!$whatsAppSuccess) {
            // WhatsApp failed, send notification via Pushover
            Log::warning("WhatsApp notification failed, sending Pushover fallback", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'chat_id' => $chatId,
                'notification_type' => $notificationType,
            ]);

            $this->pushoverService->sendWhatsAppFailureNotification(
                $notificationType,
                $user->name . ' (' . $user->mobilephone_no . ')',
                $message
            );

            return false;
        }

        return true;
    }

    /**
     * Send notification to multiple users with a role.
     */
    private function sendNotificationToRole(string $roleName, string $message, string $notificationType): int
    {
        $users = User::role($roleName)->where('active', true)->get();
        $successCount = 0;

        foreach ($users as $user) {
            if ($this->sendNotificationToUser($user, $message, $notificationType)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Format mobile phone number to WhatsApp chat ID format.
     */
    private function formatWhatsAppChatId(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Ensure it starts with country code (assume Indonesia +62 if not present)
        if (!str_starts_with($cleaned, '62')) {
            // Remove leading 0 if present
            $cleaned = ltrim($cleaned, '0');
            $cleaned = '62' . $cleaned;
        }

        return $cleaned . '@c.us';
    }

    /**
     * Send notification about schedule alert to General Affairs staff.
     */
    public function notifyScheduleAlert(CleaningScheduleAlert $alert): void
    {
        $schedule = $alert->cleaningSchedule;
        $item = $alert->cleaningScheduleItem;
        $asset = $alert->asset;

        $alertTypeText = match($alert->alert_type) {
            'asset_inactive' => 'Asset Inactive',
            'asset_disposed' => 'Asset Disposed',
            default => 'Unknown Issue',
        };

        $message = "🚨 *Cleaning Schedule Alert*\n\n";
        $message .= "*Alert Type:* {$alertTypeText}\n";
        $message .= "*Schedule:* {$schedule->schedule_name}\n";
        $message .= "*Location:* {$schedule->location->name}\n";
        
        if ($asset) {
            $message .= "*Asset:* {$asset->code} - {$asset->name}\n";
        } else {
            $message .= "*Item:* {$item->item_name}\n";
        }
        
        $message .= "*Detected:* {$alert->detected_at->format('d M Y H:i')}\n\n";
        $message .= "⚠️ Tasks will not be generated until this is resolved.";

        $this->sendNotificationToRole('General Affairs', $message, 'Cleaning Schedule Alert');
    }

    /**
     * Send notification to cleaner about assigned task.
     */
    public function notifyTaskAssigned(CleaningTask $task): void
    {
        $user = User::find($task->assigned_to);
        
        if (!$user) {
            return;
        }

        $schedule = $task->cleaningSchedule;
        $location = $task->location;

        $message = "✅ *New Cleaning Task Assigned*\n\n";
        $message .= "*Task Number:* {$task->task_number}\n";
        $message .= "*Schedule:* {$schedule->schedule_name}\n";
        $message .= "*Location:* {$location->name}\n";
        
        if ($task->asset) {
            $message .= "*Asset:* {$task->asset->code} - {$task->asset->name}\n";
        } else {
            $message .= "*Item:* {$task->item_name}\n";
        }
        
        $message .= "*Scheduled:* {$task->scheduled_date->format('d M Y H:i')}\n\n";
        $message .= "📱 Please complete this task on time.";

        $this->sendNotificationToUser($user, $message, 'Task Assignment');
    }

    /**
     * Send reminder for pending tasks.
     */
    public function notifyPendingTaskReminder(CleaningTask $task): void
    {
        $user = User::find($task->assigned_to);
        
        if (!$user) {
            return;
        }

        $message = "⏰ *Cleaning Task Reminder*\n\n";
        $message .= "*Task Number:* {$task->task_number}\n";
        $message .= "*Location:* {$task->location->name}\n";
        
        if ($task->asset) {
            $message .= "*Asset:* {$task->asset->code}\n";
        } else {
            $message .= "*Item:* {$task->item_name}\n";
        }
        
        $message .= "*Due:* {$task->scheduled_date->format('d M Y H:i')}\n\n";
        $message .= "⚠️ Please complete this task soon!";

        $this->sendNotificationToUser($user, $message, 'Task Reminder');
    }

    /**
     * Send notification about flagged submission for review.
     */
    public function notifyFlaggedForReview(CleaningApproval $approval): void
    {
        $submission = $approval->cleaningSubmission;
        $task = $submission->cleaningTask;

        $message = "🔍 *Cleaning Task Flagged for Review*\n\n";
        $message .= "*Task Number:* {$task->task_number}\n";
        $message .= "*Location:* {$task->location->name}\n";
        $message .= "*Submitted:* {$submission->submitted_at->format('d M Y H:i')}\n\n";
        $message .= "📋 This task requires your review before batch approval.";

        $this->sendNotificationToRole('General Affairs Supervisor', $message, 'Flagged for Review');
    }

    /**
     * Send notification about missed tasks.
     */
    public function notifyMissedTasks(int $missedCount, Carbon $date): void
    {
        if ($missedCount === 0) {
            return;
        }

        $message = "⚠️ *Missed Cleaning Tasks Alert*\n\n";
        $message .= "*Date:* {$date->format('d M Y')}\n";
        $message .= "*Missed Tasks:* {$missedCount}\n\n";
        $message .= "🔔 Please review and take necessary action.";

        $this->sendNotificationToRole('General Affairs Supervisor', $message, 'Missed Tasks Alert');
    }
}

