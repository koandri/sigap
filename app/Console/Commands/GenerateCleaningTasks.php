<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CleaningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class GenerateCleaningTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleaning:generate-tasks 
                            {--date= : Specific date to generate tasks for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate cleaning tasks from schedules';

    /**
     * Execute the console command.
     */
    public function handle(CleaningService $cleaningService): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? \Carbon\Carbon::parse($dateOption) : today();

        $this->info("Generating cleaning tasks for {$date->toDateString()}...");

        try {
            // Generate daily tasks
            $taskCount = $cleaningService->generateDailyTasks($date);

            // Mark yesterday's missed tasks (only if generating for today)
            if ($date->isToday()) {
                $missedCount = $cleaningService->markMissedTasks();
                
                // Flag random tasks for review
                $flaggedCount = $cleaningService->flagRandomTasksForReview();
                
                // Release inactive tasks
                $releasedCount = $cleaningService->releaseInactiveTasks();

                $this->info("✓ Generated {$taskCount} cleaning task(s)");
                $this->info("✓ Marked {$missedCount} task(s) as missed");
                $this->info("✓ Flagged {$flaggedCount} submission(s) for review");
                $this->info("✓ Released {$releasedCount} inactive task(s)");
                
                Log::info("Generated cleaning tasks", [
                    'command' => 'cleaning:generate-tasks',
                    'date' => $date->toDateString(),
                    'tasks_generated' => $taskCount,
                    'tasks_missed' => $missedCount,
                    'submissions_flagged' => $flaggedCount,
                    'tasks_released' => $releasedCount,
                ]);
            } else {
                $this->info("✓ Generated {$taskCount} cleaning task(s)");
                
                Log::info("Generated cleaning tasks for specific date", [
                    'command' => 'cleaning:generate-tasks',
                    'date' => $date->toDateString(),
                    'tasks_generated' => $taskCount,
                ]);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate cleaning tasks: ' . $e->getMessage());
            
            Log::error('Failed to generate cleaning tasks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
