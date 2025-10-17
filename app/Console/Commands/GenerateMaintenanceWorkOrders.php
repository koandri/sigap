<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class GenerateMaintenanceWorkOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:generate-work-orders 
                            {--force : Force generation even if not overdue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate work orders from overdue maintenance schedules';

    /**
     * Execute the console command.
     */
    public function handle(MaintenanceService $maintenanceService): int
    {
        $this->info('Checking for overdue maintenance schedules...');

        try {
            $count = $maintenanceService->generateWorkOrdersFromSchedules();

            if ($count > 0) {
                $this->info("✓ Successfully generated {$count} work order(s) from maintenance schedules.");
                
                // Log for monitoring
                Log::info("Generated {$count} work orders from maintenance schedules", [
                    'command' => 'maintenance:generate-work-orders',
                    'timestamp' => now(),
                ]);
            } else {
                $this->info('No overdue maintenance schedules found.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate work orders: ' . $e->getMessage());
            
            // Log the error
            Log::error('Failed to generate work orders from maintenance schedules', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}

