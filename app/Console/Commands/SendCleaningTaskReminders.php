<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CleaningTask;
use App\Services\CleaningService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class SendCleaningTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleaning:send-reminders 
                            {--hours=2 : Hours before scheduled time to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for upcoming cleaning tasks';

    /**
     * Execute the console command.
     */
    public function handle(CleaningService $cleaningService): int
    {
        $hours = (int) $this->option('hours');
        $reminderTime = now()->addHours($hours);

        $this->info("Sending reminders for tasks scheduled within {$hours} hour(s)...");

        try {
            // Get pending tasks scheduled within the next X hours
            $tasks = CleaningTask::where('status', 'pending')
                ->whereBetween('scheduled_date', [now(), $reminderTime])
                ->with(['cleaningSchedule', 'location', 'asset'])
                ->get();

            if ($tasks->isEmpty()) {
                $this->info('No tasks found that need reminders.');
                return self::SUCCESS;
            }

            $remindersSent = 0;

            foreach ($tasks as $task) {
                $cleaningService->notifyPendingTaskReminder($task);
                $remindersSent++;
            }

            $this->info("✓ Sent {$remindersSent} task reminder(s)");

            Log::info('Sent cleaning task reminders', [
                'command' => 'cleaning:send-reminders',
                'hours' => $hours,
                'reminders_sent' => $remindersSent,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to send reminders: ' . $e->getMessage());

            Log::error('Failed to send cleaning task reminders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}

