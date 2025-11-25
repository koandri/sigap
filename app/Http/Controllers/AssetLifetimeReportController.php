<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\AssetLifetimeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class AssetLifetimeReportController extends Controller
{
    public function __construct(
        private readonly AssetLifetimeService $lifetimeService
    ) {}

    /**
     * Display lifetime metrics dashboard.
     */
    public function index(Request $request): View
    {
        $categories = AssetCategory::active()->orderBy('name')->get();
        
        // Calculate metrics on-the-fly
        $categoryMetrics = [];
        foreach ($categories as $category) {
            // Get disposed assets with lifetime values
            $disposedAssets = $category->assets()
                ->whereNotNull('actual_lifetime_value')
                ->whereNotNull('lifetime_unit')
                ->get();

            if ($disposedAssets->isEmpty()) {
                continue;
            }

            // Group by lifetime unit
            $grouped = $disposedAssets->groupBy(fn($asset) => $asset->lifetime_unit->value);

            foreach ($grouped as $unitValue => $assets) {
                $categoryMetrics[$category->id][$unitValue] = [
                    'average_lifetime' => $assets->avg('actual_lifetime_value'),
                    'sample_size' => $assets->count(),
                    'min_lifetime' => $assets->min('actual_lifetime_value'),
                    'max_lifetime' => $assets->max('actual_lifetime_value'),
                ];
            }
        }

        return view('reports.asset-lifetime.index', compact('categories', 'categoryMetrics'));
    }

    /**
     * Display category-specific metrics.
     */
    public function categoryMetrics(AssetCategory $category): View
    {
        $disposedAssets = $category->assets()
            ->with(['parentAsset'])
            ->whereNotNull('actual_lifetime_value')
            ->orderByDesc('disposed_date')
            ->paginate(20);

        return view('reports.asset-lifetime.category', compact('category', 'disposedAssets'));
    }

    /**
     * Display individual asset lifetime report.
     */
    public function assetLifetimeReport(Asset $asset): View
    {
        $asset->load(['assetCategory']);
        $lifetimePercentage = $this->lifetimeService->getLifetimePercentage($asset);
        $remainingLifetime = $this->lifetimeService->getRemainingLifetime($asset);
        $expectedLifetime = $this->lifetimeService->getExpectedLifetime($asset);
        $suggestedLifetime = $this->lifetimeService->suggestExpectedLifetime($asset);
        $actualLifetime = $this->lifetimeService->calculateActualLifetime($asset);

        return view('reports.asset-lifetime.asset', compact(
            'asset',
            'lifetimePercentage',
            'remainingLifetime',
            'expectedLifetime',
            'suggestedLifetime',
            'actualLifetime'
        ));
    }

    /**
     * Export metrics to CSV/Excel.
     */
    public function exportMetrics(Request $request): Response
    {
        // TODO: Implement export functionality
        return response('Export functionality coming soon', 200);
    }
}
