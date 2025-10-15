<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenanceLog;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

final class MaintenanceReportController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {
        $this->middleware('can:maintenance.reports.view');
    }

    /**
     * Display maintenance reports.
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->subMonth();
            
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now();

        $assetId = $request->filled('asset_id') ? $request->asset_id : null;
        $categoryId = $request->filled('category_id') ? $request->category_id : null;

        // Get work orders for the period
        $workOrdersQuery = WorkOrder::with(['asset.assetCategory', 'maintenanceType', 'assignedUser'])
            ->whereBetween('completed_date', [$startDate, $endDate]);

        if ($assetId) {
            $workOrdersQuery->where('asset_id', $assetId);
        }

        if ($categoryId) {
            $workOrdersQuery->whereHas('asset', function ($query) use ($categoryId) {
                $query->where('asset_category_id', $categoryId);
            });
        }

        $workOrders = $workOrdersQuery->get();

        // Calculate statistics
        $totalWorkOrders = $workOrders->count();
        $totalCost = $workOrders->sum(function ($wo) {
            return $wo->parts->sum(function ($part) {
                return $part->quantity_used * ($part->item->price ?? 0);
            });
        });
        $totalHours = $workOrders->sum('actual_hours');
        $avgHours = $workOrders->avg('actual_hours');

        // Maintenance type breakdown
        $maintenanceTypeBreakdown = $workOrders->groupBy('maintenanceType.name')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'cost' => $group->sum(function ($wo) {
                        return $wo->parts->sum(function ($part) {
                            return $part->quantity_used * ($part->item->price ?? 0);
                        });
                    }),
                    'hours' => $group->sum('actual_hours')
                ];
            });

        // Asset breakdown
        $assetBreakdown = $workOrders->groupBy('asset.name')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'cost' => $group->sum(function ($wo) {
                        return $wo->parts->sum(function ($part) {
                            return $part->quantity_used * ($part->item->price ?? 0);
                        });
                    }),
                    'hours' => $group->sum('actual_hours')
                ];
            });

        // Monthly trend data
        $monthlyTrend = $workOrders->groupBy(function ($wo) {
            return $wo->completed_date->format('Y-m');
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'cost' => $group->sum(function ($wo) {
                    return $wo->parts->sum(function ($part) {
                        return $part->quantity_used * ($part->item->price ?? 0);
                    });
                })
            ];
        });

        $assets = Asset::active()->orderBy('name')->get();
        $categories = \App\Models\AssetCategory::active()->orderBy('name')->get();

        return view('maintenance.reports.index', compact(
            'workOrders',
            'totalWorkOrders',
            'totalCost',
            'totalHours',
            'avgHours',
            'maintenanceTypeBreakdown',
            'assetBreakdown',
            'monthlyTrend',
            'startDate',
            'endDate',
            'assetId',
            'categoryId',
            'assets',
            'categories'
        ));
    }
}
