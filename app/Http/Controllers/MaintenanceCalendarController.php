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
                'title' => $schedule->asset->name . ' - ' . $schedule->maintenanceType->name,
                'start' => $schedule->next_due_date->format('Y-m-d'),
                'color' => $schedule->next_due_date < now() ? '#dc3545' : '#28a745',
                'extendedProps' => [
                    'type' => 'schedule',
                    'asset' => $schedule->asset->name,
                    'maintenance_type' => $schedule->maintenanceType->name,
                    'description' => $schedule->description,
                    'url' => route('maintenance.schedules.show', $schedule)
                ]
            ]);
        }

        // Get work orders
        $workOrders = WorkOrder::with(['asset', 'maintenanceType'])
            ->whereNotNull('scheduled_date')
            ->whereBetween('scheduled_date', [$start, $end])
            ->get();

        foreach ($workOrders as $workOrder) {
            $color = match($workOrder->priority) {
                'urgent' => '#dc3545',
                'high' => '#fd7e14',
                'medium' => '#ffc107',
                'low' => '#6c757d',
                default => '#6c757d'
            };

            $events->push([
                'id' => 'workorder_' . $workOrder->id,
                'title' => $workOrder->asset->name . ' - ' . $workOrder->maintenanceType->name,
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
