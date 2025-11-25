<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AssetCategory;
use App\Models\AssetCategoryUsageType;
use App\Services\AssetLifetimeService;
use Illuminate\Console\Command;

final class RecalculateLifetimeMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:recalculate-lifetime-metrics 
                            {--category= : Specific category ID to recalculate}
                            {--usage-type= : Specific usage type ID to recalculate}
                            {--all : Recalculate all metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate lifetime metrics for asset categories and usage types';

    public function __construct(
        private readonly AssetLifetimeService $lifetimeService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $categoryId = $this->option('category');
        $usageTypeId = $this->option('usage-type');
        $all = $this->option('all');

        if ($all) {
            $this->info('Recalculating all lifetime metrics...');
            $categories = AssetCategory::all();
            $total = 0;

            foreach ($categories as $category) {
                $this->lifetimeService->recalculateCategoryMetrics($category->id);
                $total++;
            }

            $this->info("Recalculated metrics for {$total} categories.");
            return Command::SUCCESS;
        }

        if ($categoryId) {
            $category = AssetCategory::find($categoryId);
            if (!$category) {
                $this->error("Category with ID {$categoryId} not found.");
                return Command::FAILURE;
            }

            $this->info("Recalculating metrics for category: {$category->name}");
            $this->lifetimeService->recalculateCategoryMetrics($categoryId, $usageTypeId);
            $this->info('Metrics recalculated successfully.');
            return Command::SUCCESS;
        }

        if ($usageTypeId) {
            $usageType = AssetCategoryUsageType::find($usageTypeId);
            if (!$usageType) {
                $this->error("Usage type with ID {$usageTypeId} not found.");
                return Command::FAILURE;
            }

            $this->info("Recalculating metrics for usage type: {$usageType->name}");
            $this->lifetimeService->recalculateCategoryMetrics($usageType->asset_category_id, $usageTypeId);
            $this->info('Metrics recalculated successfully.');
            return Command::SUCCESS;
        }

        $this->error('Please specify --category, --usage-type, or --all option.');
        return Command::FAILURE;
    }
}
