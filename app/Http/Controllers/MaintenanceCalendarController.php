<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MaintenanceCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:maintenance.dashboard.view');
    }

    /**
     * Display the maintenance calendar.
     */
    public function index(): View
    {
        return view('maintenance.calendar');
    }

    /**
     * Get calendar events for FullCalendar.
     */
    public function events(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $events = collect();

        // Get maintenance schedules
        $schedules = MaintenanceSchedule::active()
            ->with(['asset', 'maintenanceType'])
            ->whereBetween('next_due_date', [$start, $end])
            ->get();

        foreach ($schedules as $schedule) {
            $events->push([
                'id' => 'schedule_' . $schedule->id,
                'title' => $schedule->asset->name,
                'start' => $schedule->next_due_date->format('Y-m-d'),
                'color' => '#206bc4', // Primary color
                'extendedProps' => [
                    'type' => 'schedule',
                    'asset' => $schedule->asset->name,
                    'maintenance_type' => $schedule->maintenanceType->name,
                    'description' => $schedule->description
                ]
            ]);
        }

        // Get work orders
        $workOrders = WorkOrder::with(['asset', 'maintenanceType'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$start, $end])
            ->get();

        foreach ($workOrders as $workOrder) {
            // Color work orders by status
            $color = match($workOrder->status) {
                'submitted' => '#6c757d',      // Secondary (gray)
                'assigned' => '#0d6efd',       // Info (blue)
                'in-progress' => '#fd7e14',    // Warning (orange)
                'pending-verification' => '#0054a6', // Primary (darker blue)
                'verified' => '#28a745',       // Success (green)
                'completed' => '#28a745',      // Success (green)
                'rework' => '#dc3545',         // Danger (red)
                'cancelled' => '#495057',      // Dark (muted gray)
                default => '#6c757d'
            };

            $events->push([
                'id' => 'workorder_' . $workOrder->id,
                'title' => $workOrder->wo_number,
                'start' => $workOrder->scheduled_date->format('Y-m-d'),
                'color' => $color,
                'extendedProps' => [
                    'type' => 'workorder',
                    'wo_number' => $workOrder->wo_number,
                    'asset' => $workOrder->asset->name,
                    'maintenance_type' => $workOrder->maintenanceType->name,
                    'priority' => $workOrder->priority,
                    'status' => $workOrder->status,
                    'url' => route('maintenance.work-orders.show', $workOrder)
                ]
            ]);
        }

        return response()->json($events);
    }
}
