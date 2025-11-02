<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningTask;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FacilityManagementReportController extends Controller
{
    /**
     * Display daily report for selected locations.
     */
    public function dailyReport(Request $request): View
    {
        $this->authorize('facility.reports.view');

        $date = $request->input('date', today()->toDateString());
        $locationIds = $request->input('location_id', []);

        $allLocations = Location::active()->orderBy('name')->get();

        $selectedLocations = collect();
        $locationData = [];
        $totalStats = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'missed' => 0,
        ];

        if (!empty($locationIds)) {
            $selectedLocations = Location::whereIn('id', $locationIds)->orderBy('name')->get();
            
            foreach ($selectedLocations as $location) {
                $tasks = CleaningTask::whereDate('scheduled_date', $date)
                    ->where('location_id', $location->id)
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

                // Add to total stats
                $totalStats['total'] += $stats['total'];
                $totalStats['completed'] += $stats['completed'];
                $totalStats['pending'] += $stats['pending'];
                $totalStats['missed'] += $stats['missed'];

                $locationData[] = [
                    'location' => $location,
                    'tasks' => $tasks,
                    'stats' => $stats,
                ];
            }
        }

        return view('reports.facility.daily', compact('allLocations', 'selectedLocations', 'locationData', 'date', 'totalStats', 'locationIds'));
    }

    /**
     * Display daily report in print-friendly format (use browser print to save as PDF).
     */
    public function dailyReportPdf(Request $request): View
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

        return view('reports.facility.daily-pdf', compact('location', 'tasks', 'date', 'stats'));
    }

    /**
     * Display weekly report grid.
     */
    public function weeklyReport(Request $request): View
    {
        $this->authorize('facility.reports.view');

        // Get date parameter and ensure it's a Monday
        $date = $request->input('date', now()->startOfWeek()->toDateString());
        $weekStart = \Carbon\Carbon::parse($date)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $locationIds = $request->input('locations', []);

        // Get locations
        $locations = $this->getFilteredLocations($locationIds);
        $allLocations = Location::active()->orderBy('name')->get(); // For filter

        // Build grid data using shared method
        $gridData = $this->buildWeeklyGridData($locations, $weekStart);

        return view('reports.facility.weekly', compact(
            'gridData',
            'weekStart',
            'weekEnd',
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
     * Display weekly report in print-friendly format (use browser print to save as PDF).
     */
    public function weeklyReportPdf(Request $request): View
    {
        $this->authorize('facility.reports.view');

        $date = $request->input('date', now()->startOfWeek()->toDateString());
        $weekStart = \Carbon\Carbon::parse($date)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $locationIds = $request->input('locations', []);

        // Get locations using shared method
        $locations = $this->getFilteredLocations($locationIds);

        // Build grid data using shared method
        $gridData = $this->buildWeeklyGridData($locations, $weekStart);

        return view('reports.facility.weekly-pdf', compact('gridData', 'weekStart', 'weekEnd'));
    }

    /**
     * Get filtered locations based on location IDs or return all active locations.
     */
    private function getFilteredLocations(array $locationIds)
    {
        if (empty($locationIds)) {
            return Location::active()->orderBy('name')->get();
        }
        
        return Location::whereIn('id', $locationIds)->orderBy('name')->get();
    }

    /**
     * Build weekly grid data for locations and week period.
     * This method is shared between display and print views to ensure consistency.
     */
    private function buildWeeklyGridData($locations, \Carbon\Carbon $weekStart): array
    {
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
                if ($total === 0) {
                    // No tasks scheduled for this day/location
                    $indicator = '-';
                } elseif ($completed === $total) {
                    // All tasks completed
                    $indicator = '✓';
                } elseif ($completed > 0) {
                    // Some tasks completed
                    $indicator = '⚠';
                } else {
                    // No tasks completed (but tasks exist)
                    $indicator = '✗';
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

        return $gridData;
    }
}

