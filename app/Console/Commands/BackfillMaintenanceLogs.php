<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\WorkOrder;
use App\Models\MaintenanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BackfillMaintenanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:backfill-logs 
                            {--dry-run : Show what would be done without actually doing it}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill maintenance logs from existing completed work orders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for completed work orders without maintenance logs...');

        try {
            // Find completed work orders that don't have maintenance logs
            $workOrders = WorkOrder::where('status', 'completed')
                ->whereDoesntHave('maintenanceLogs')
                ->with(['asset', 'assignedUser', 'actions', 'progressLogs'])
                ->orderBy('completed_date')
                ->get();

            if ($workOrders->isEmpty()) {
                $this->info('No work orders found that need maintenance logs.');
                return self::SUCCESS;
            }

            $this->info("Found {$workOrders->count()} completed work order(s) without maintenance logs.");

            // Show preview
            $this->newLine();
            $this->table(
                ['WO Number', 'Asset', 'Completed Date', 'Assigned To'],
                $workOrders->map(function ($wo) {
                    return [
                        $wo->wo_number,
                        $wo->asset->name ?? 'N/A',
                        $wo->completed_date?->format('Y-m-d H:i') ?? 'N/A',
                        $wo->assignedUser?->name ?? 'Unassigned',
                    ];
                })->toArray()
            );

            if ($this->option('dry-run')) {
                $this->warn('DRY RUN MODE: No changes will be made.');
                return self::SUCCESS;
            }

            // Ask for confirmation unless --force is used
            if (!$this->option('force')) {
                if (!$this->confirm('Do you want to create maintenance logs for these work orders?', true)) {
                    $this->info('Operation cancelled.');
                    return self::SUCCESS;
                }
            }

            $this->newLine();
            $this->info('Creating maintenance logs...');
            
            $progressBar = $this->output->createProgressBar($workOrders->count());
            $progressBar->start();

            $created = 0;
            $errors = 0;

            DB::beginTransaction();

            try {
                foreach ($workOrders as $workOrder) {
                    try {
                        // Aggregate action descriptions from work order actions
                        $actionDescriptions = $workOrder->actions()
                            ->orderBy('performed_at')
                            ->get()
                            ->pluck('action_description')
                            ->filter()
                            ->join('; ');
                        
                        // Aggregate progress notes
                        $progressNotes = $workOrder->progressLogs()
                            ->orderBy('logged_at')
                            ->get()
                            ->pluck('progress_notes')
                            ->filter()
                            ->join('; ');
                        
                        // Combine action taken from various sources
                        $actionTaken = collect([
                            $workOrder->description,
                            $actionDescriptions,
                            $progressNotes,
                            $workOrder->notes
                        ])->filter()->join(' | ');

                        $workOrder->maintenanceLogs()->create([
                            'asset_id' => $workOrder->asset_id,
                            'performed_by' => $workOrder->assigned_to ?? $workOrder->requested_by ?? 1,
                            'performed_at' => $workOrder->work_finished_at ?? $workOrder->completed_date ?? now(),
                            'action_taken' => !empty($actionTaken) ? $actionTaken : 'Work order completed',
                            'findings' => $workOrder->verification_notes,
                            'recommendations' => null,
                            'cost' => 0,
                        ]);

                        $created++;
                    } catch (\Exception $e) {
                        $this->error("\nFailed to create log for WO {$workOrder->wo_number}: " . $e->getMessage());
                        $errors++;
                    }

                    $progressBar->advance();
                }

                DB::commit();
                
                $progressBar->finish();
                $this->newLine(2);

                $this->info("✓ Successfully created {$created} maintenance log(s).");
                
                if ($errors > 0) {
                    $this->warn("⚠ {$errors} error(s) occurred during the process.");
                }

                // Log for monitoring
                Log::info("Backfilled {$created} maintenance logs from completed work orders", [
                    'command' => 'maintenance:backfill-logs',
                    'timestamp' => now(),
                    'created' => $created,
                    'errors' => $errors,
                ]);

                return self::SUCCESS;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->error('Failed to backfill maintenance logs: ' . $e->getMessage());
            
            // Log the error
            Log::error('Failed to backfill maintenance logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}

