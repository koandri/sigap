<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MaintenanceDashboardController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {
        $this->middleware('can:maintenance.dashboard.view');
    }

    /**
     * Display the maintenance dashboard.
     */
    public function index(): View
    {
        // Statistics
        $totalAssets = Asset::active()->count();
        $activeWorkOrders = WorkOrder::open()->count();
        $overdueSchedules = $this->maintenanceService->getOverdueSchedules()->count();
        $upcomingSchedules = $this->maintenanceService->getUpcomingSchedules(7)->count();

        // Recent work orders
        $recentWorkOrders = WorkOrder::with(['asset', 'maintenanceType', 'assignedUser'])
            ->latest()
            ->limit(10)
            ->get();

        // Upcoming maintenance
        $upcomingMaintenance = $this->maintenanceService->getUpcomingSchedules(7);

        // Asset status distribution
        $assetStatusCounts = Asset::active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Work order priority distribution
        $workOrderPriorityCounts = WorkOrder::open()
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        return view('maintenance.dashboard', compact(
            'totalAssets',
            'activeWorkOrders',
            'overdueSchedules',
            'upcomingSchedules',
            'recentWorkOrders',
            'upcomingMaintenance',
            'assetStatusCounts',
            'workOrderPriorityCounts'
        ));
    }
}
