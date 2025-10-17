<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CleaningTask;
use App\Models\CleaningSubmission;
use App\Models\CleaningApproval;
use App\Models\CleaningScheduleAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class FacilityDashboardController extends Controller
{
    /**
     * Display facility management dashboard.
     */
    public function index(Request $request): View
    {
        $this->authorize('facility.dashboard.view');

        // Date range for statistics
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Cleaner performance ranking
        $cleanerRanking = $this->getCleanerRanking($startDate, $endDate);

        // Overall completion statistics
        $completionStats = $this->getCompletionStats($startDate, $endDate);

        // SLA compliance statistics
        $slaStats = $this->getSlaStats($startDate, $endDate);

        // Tasks by location
        $tasksByLocation = $this->getTasksByLocation($startDate, $endDate);

        // Pending approvals with SLA status
        $pendingApprovals = $this->getPendingApprovals();

        // Unresolved schedule alerts
        $unresolvedAlerts = CleaningScheduleAlert::unresolved()
            ->with(['cleaningSchedule.location', 'cleaningScheduleItem', 'asset'])
            ->latest('detected_at')
            ->get();

        // Weekly trend data
        $weeklyTrend = $this->getWeeklyTrend();

        return view('facility.dashboard', compact(
            'cleanerRanking',
            'completionStats',
            'slaStats',
            'tasksByLocation',
            'pendingApprovals',
            'unresolvedAlerts',
            'weeklyTrend',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get cleaner performance ranking.
     */
    private function getCleanerRanking(string $startDate, string $endDate): array
    {
        $cleaners = User::role('Cleaner')
            ->with(['cleaningTasks' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('scheduled_date', [$startDate, $endDate]);
            }])
            ->get();

        $ranking = $cleaners->map(function ($cleaner) use ($startDate, $endDate) {
            $totalTasks = CleaningTask::where('assigned_to', $cleaner->id)
                ->whereBetween('scheduled_date', [$startDate, $endDate])
                ->count();

            $completedTasks = CleaningTask::where('assigned_to', $cleaner->id)
                ->whereBetween('scheduled_date', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'approved'])
                ->count();

            $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

            return [
                'id' => $cleaner->id,
                'name' => $cleaner->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => round($completionRate, 2),
            ];
        })->sortByDesc('completion_rate')->values()->all();

        return $ranking;
    }

    /**
     * Get overall completion statistics.
     */
    private function getCompletionStats(string $startDate, string $endDate): array
    {
        $total = CleaningTask::whereBetween('scheduled_date', [$startDate, $endDate])->count();
        $completed = CleaningTask::whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'approved'])
            ->count();
        $pending = CleaningTask::whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'pending')
            ->count();
        $missed = CleaningTask::whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'missed')
            ->count();

        $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'missed' => $missed,
            'completion_rate' => round($completionRate, 2),
        ];
    }

    /**
     * Get SLA compliance statistics.
     */
    private function getSlaStats(string $startDate, string $endDate): array
    {
        $approvals = CleaningApproval::whereHas('cleaningSubmission', function($q) use ($startDate, $endDate) {
                $q->whereBetween('submitted_at', [$startDate, $endDate]);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->get();

        $total = $approvals->count();
        $withinSla = $approvals->filter(function ($approval) {
            $deadline = $approval->cleaningSubmission->submitted_at->addDay()->setTime(9, 0, 0);
            $approvedAt = $approval->updated_at;
            return $approvedAt->lte($deadline->addHours(24)); // Within 24 hours of deadline
        })->count();

        $complianceRate = $total > 0 ? ($withinSla / $total) * 100 : 0;

        // Average approval time in hours
        $avgApprovalTime = $approvals->avg(function ($approval) {
            return $approval->cleaningSubmission->submitted_at->diffInHours($approval->updated_at);
        });

        return [
            'total_approvals' => $total,
            'within_sla' => $withinSla,
            'compliance_rate' => round($complianceRate, 2),
            'avg_approval_hours' => round($avgApprovalTime ?? 0, 2),
        ];
    }

    /**
     * Get tasks grouped by location.
     */
    private function getTasksByLocation(string $startDate, string $endDate): array
    {
        return CleaningTask::whereBetween('scheduled_date', [$startDate, $endDate])
            ->select('location_id', DB::raw('count(*) as total'), 
                     DB::raw('sum(case when status in ("completed", "approved") then 1 else 0 end) as completed'))
            ->with('location')
            ->groupBy('location_id')
            ->get()
            ->map(function ($item) {
                $rate = $item->total > 0 ? ($item->completed / $item->total) * 100 : 0;
                return [
                    'location' => $item->location->name,
                    'total' => $item->total,
                    'completed' => $item->completed,
                    'completion_rate' => round($rate, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get pending approvals with SLA status.
     */
    private function getPendingApprovals(): array
    {
        $pending = CleaningApproval::pending()
            ->with(['cleaningSubmission.cleaningTask.location', 'cleaningSubmission.submittedByUser'])
            ->get()
            ->sortByDesc(function ($approval) {
                return $approval->hours_overdue;
            })
            ->take(10)
            ->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'task_number' => $approval->cleaningSubmission->cleaningTask->task_number,
                    'location' => $approval->cleaningSubmission->cleaningTask->location->name,
                    'submitted_by' => $approval->cleaningSubmission->submittedByUser->name,
                    'submitted_at' => $approval->cleaningSubmission->submitted_at,
                    'hours_overdue' => $approval->hours_overdue,
                    'sla_status' => $approval->sla_status,
                    'sla_color' => $approval->sla_color,
                    'is_flagged' => $approval->is_flagged_for_review,
                ];
            })
            ->values()
            ->all();

        return $pending;
    }

    /**
     * Get weekly trend data.
     */
    private function getWeeklyTrend(): array
    {
        $weeks = [];
        for ($i = 6; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $total = CleaningTask::whereBetween('scheduled_date', [$weekStart, $weekEnd])->count();
            $completed = CleaningTask::whereBetween('scheduled_date', [$weekStart, $weekEnd])
                ->whereIn('status', ['completed', 'approved'])
                ->count();

            $weeks[] = [
                'week' => $weekStart->format('M d'),
                'total' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
        }

        return $weeks;
    }
}
