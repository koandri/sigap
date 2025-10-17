<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningTask;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

final class CleaningReportController extends Controller
{
    /**
     * Display daily report for a location.
     */
    public function dailyReport(Request $request): View
    {
        $this->authorize('facility.reports.view');

        $date = $request->input('date', today()->toDateString());
        $locationId = $request->input('location_id');

        $locations = Location::active()->orderBy('name')->get();

        $location = null;
        $tasks = collect();

        if ($locationId) {
            $location = Location::findOrFail($locationId);
            
            $tasks = CleaningTask::whereDate('scheduled_date', $date)
                ->where('location_id', $locationId)
                ->with([
                    'cleaningSchedule',
                    'asset',
                    'assignedUser',
                    'completedByUser',
                    'submission'
                ])
                ->orderBy('item_name')
                ->get();
        }

        // Statistics
        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->whereIn('status', ['completed', 'approved'])->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'missed' => $tasks->where('status', 'missed')->count(),
        ];

        return view('facility.reports.daily', compact('locations', 'location', 'tasks', 'date', 'stats'));
    }

    /**
     * Generate daily report PDF.
     */
    public function dailyReportPdf(Request $request): Response
    {
        $this->authorize('facility.reports.view');

        $date = $request->input('date', today()->toDateString());
        $locationId = $request->input('location_id');

        $location = Location::findOrFail($locationId);
        
        $tasks = CleaningTask::whereDate('scheduled_date', $date)
            ->where('location_id', $locationId)
            ->with([
                'cleaningSchedule',
                'asset',
                'assignedUser',
                'completedByUser',
                'submission'
            ])
            ->orderBy('item_name')
            ->get();

        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->whereIn('status', ['completed', 'approved'])->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'missed' => $tasks->where('status', 'missed')->count(),
        ];

        $pdf = Pdf::loadView('facility.reports.daily-pdf', compact('location', 'tasks', 'date', 'stats'));
        
        return $pdf->download("cleaning-report-{$location->name}-{$date}.pdf");
    }

    /**
     * Display weekly report grid.
     */
    public function weeklyReport(Request $request): View
    {
        $this->authorize('facility.reports.view');

        $year = $request->input('year', now()->year);
        $week = $request->input('week', now()->week);
        $locationIds = $request->input('locations', []);

        // Calculate week start and end dates
        $weekStart = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get locations
        if (empty($locationIds)) {
            $locations = Location::active()->orderBy('name')->get();
        } else {
            $locations = Location::whereIn('id', $locationIds)->orderBy('name')->get();
        }

        $allLocations = Location::active()->orderBy('name')->get(); // For filter

        // Build grid data
        $gridData = [];
        foreach ($locations as $location) {
            $row = [
                'location' => $location,
                'days' => [],
            ];

            for ($i = 0; $i < 7; $i++) {
                $date = $weekStart->copy()->addDays($i);
                
                $tasks = CleaningTask::whereDate('scheduled_date', $date)
                    ->where('location_id', $location->id)
                    ->get();

                $total = $tasks->count();
                $completed = $tasks->whereIn('status', ['completed', 'approved'])->count();
                
                // Determine status indicator
                $indicator = '✗'; // none done
                if ($total > 0) {
                    if ($completed === $total) {
                        $indicator = '✓'; // all done
                    } elseif ($completed > 0) {
                        $indicator = '⚠'; // partial
                    }
                }

                $row['days'][] = [
                    'date' => $date->toDateString(),
                    'total' => $total,
                    'completed' => $completed,
                    'indicator' => $indicator,
                ];
            }

            $gridData[] = $row;
        }

        return view('facility.reports.weekly', compact(
            'gridData',
            'weekStart',
            'weekEnd',
            'year',
            'week',
            'allLocations',
            'locationIds'
        ));
    }

    /**
     * Get cell details for a specific date and location (AJAX).
     */
    public function cellDetails(Request $request)
    {
        $this->authorize('facility.reports.view');

        $date = $request->input('date');
        $locationId = $request->input('location_id');

        $location = Location::findOrFail($locationId);
        
        $tasks = CleaningTask::whereDate('scheduled_date', $date)
            ->where('location_id', $locationId)
            ->with(['cleaningSchedule', 'asset', 'assignedUser', 'completedByUser'])
            ->orderBy('item_name')
            ->get();

        return response()->json([
            'location' => $location->name,
            'date' => $date,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'task_number' => $task->task_number,
                    'item_name' => $task->item_name,
                    'status' => $task->status,
                    'assigned_to' => $task->assignedUser?->name,
                    'completed_by' => $task->completedByUser?->name,
                    'completed_at' => $task->completed_at?->format('H:i'),
                ];
            }),
        ]);
    }

    /**
     * Generate weekly report PDF (A4 landscape).
     */
    public function weeklyReportPdf(Request $request): Response
    {
        $this->authorize('facility.reports.view');

        $year = $request->input('year', now()->year);
        $week = $request->input('week', now()->week);
        $locationIds = $request->input('locations', []);

        $weekStart = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        if (empty($locationIds)) {
            $locations = Location::active()->orderBy('name')->get();
        } else {
            $locations = Location::whereIn('id', $locationIds)->orderBy('name')->get();
        }

        // Build grid data
        $gridData = [];
        foreach ($locations as $location) {
            $row = [
                'location' => $location,
                'days' => [],
            ];

            for ($i = 0; $i < 7; $i++) {
                $date = $weekStart->copy()->addDays($i);
                
                $tasks = CleaningTask::whereDate('scheduled_date', $date)
                    ->where('location_id', $location->id)
                    ->get();

                $total = $tasks->count();
                $completed = $tasks->whereIn('status', ['completed', 'approved'])->count();
                
                $indicator = '✗';
                if ($total > 0) {
                    if ($completed === $total) {
                        $indicator = '✓';
                    } elseif ($completed > 0) {
                        $indicator = '⚠';
                    }
                }

                $row['days'][] = [
                    'date' => $date->format('m/d'),
                    'total' => $total,
                    'completed' => $completed,
                    'indicator' => $indicator,
                ];
            }

            $gridData[] = $row;
        }

        $pdf = Pdf::loadView('facility.reports.weekly-pdf', compact('gridData', 'weekStart', 'weekEnd'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download("cleaning-weekly-report-week{$week}-{$year}.pdf");
    }
}
