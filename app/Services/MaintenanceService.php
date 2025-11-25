<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\MaintenanceLog;
use App\Models\PositionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class MaintenanceService
{
    /**
     * Generate work orders from preventive maintenance schedules.
     */
    public function generateWorkOrdersFromSchedules(): int
    {
        $overdueSchedules = MaintenanceSchedule::active()
            ->overdue()
            ->with(['asset', 'maintenanceType', 'assignedUser'])
            ->get();

        $generatedCount = 0;

        foreach ($overdueSchedules as $schedule) {
            // Check if there's already an open work order for this schedule
            $existingWO = WorkOrder::where('asset_id', $schedule->asset_id)
                ->where('maintenance_type_id', $schedule->maintenance_type_id)
                ->whereIn('status', ['submitted', 'assigned', 'in-progress', 'pending-verification'])
                ->first();

            if (!$existingWO) {
                // Create work order with auto-assignment for scheduled maintenance
                $workOrder = WorkOrder::create([
                    'wo_number' => $this->generateWONumber(),
                    'asset_id' => $schedule->asset_id,
                    'maintenance_type_id' => $schedule->maintenance_type_id,
                    'priority' => 'medium',
                    'status' => 'assigned', // Auto-assign scheduled maintenance
                    'scheduled_date' => $schedule->next_due_date,
                    'assigned_to' => $schedule->assigned_to,
                    'assigned_by' => $schedule->assigned_to, // Self-assigned from schedule
                    'assigned_at' => now(),
                    'requested_by' => 1, // System user (ID 1) for scheduled maintenance
                    'description' => $schedule->description,
                ]);

                $generatedCount++;
            }
        }

        return $generatedCount;
    }

    /**
     * Calculate next due date for a maintenance schedule.
     */
    public function calculateNextDueDate(MaintenanceSchedule $schedule): Carbon
    {
        $baseDate = $schedule->last_performed_at ?? $schedule->created_at ?? now();
        $config = $schedule->frequency_config ?? [];
        
        return match($schedule->frequency_type->value) {
            'hourly' => $this->calculateHourlyNextDate($baseDate, $config),
            'daily' => $this->calculateDailyNextDate($baseDate, $config, $schedule->frequency_days),
            'weekly' => $this->calculateWeeklyNextDate($baseDate, $config),
            'monthly' => $this->calculateMonthlyNextDate($baseDate, $config),
            'yearly' => $this->calculateYearlyNextDate($baseDate, $config),
            default => $baseDate->copy()->addDays($schedule->frequency_days ?? 1),
        };
    }

    private function calculateHourlyNextDate(Carbon $baseDate, array $config): Carbon
    {
        $interval = (int) ($config['interval'] ?? 1);
        return $baseDate->copy()->addHours($interval);
    }

    private function calculateDailyNextDate(Carbon $baseDate, array $config, ?int $fallbackDays): Carbon
    {
        $interval = (int) ($config['interval'] ?? $fallbackDays ?? 1);
        return $baseDate->copy()->addDays($interval);
    }

    private function calculateWeeklyNextDate(Carbon $baseDate, array $config): Carbon
    {
        $interval = (int) ($config['interval'] ?? 1);
        $days = array_map('intval', $config['days'] ?? []);
        
        if (empty($days)) {
            return $baseDate->copy()->addWeeks($interval);
        }
        
        // Find next occurrence of specified day(s)
        $nextDate = $baseDate->copy()->addDay();
        $weeksAdded = 0;
        
        while (true) {
            $currentDayOfWeek = $nextDate->dayOfWeekIso; // 1=Monday, 7=Sunday
            
            if (in_array($currentDayOfWeek, $days, true)) {
                // Check if we're in the right week interval
                $weeksDiff = $nextDate->diffInWeeks($baseDate);
                if ($weeksDiff % $interval === 0) {
                    return $nextDate;
                }
            }
            
            $nextDate->addDay();
            
            // Safety check to prevent infinite loop
            if ($nextDate->diffInDays($baseDate) > 365) {
                return $baseDate->copy()->addWeeks($interval);
            }
        }
    }

    private function calculateMonthlyNextDate(Carbon $baseDate, array $config): Carbon
    {
        $interval = (int) ($config['interval'] ?? 1);
        $type = $config['type'] ?? 'date';
        
        $nextDate = $baseDate->copy()->addMonths($interval);
        
        if ($type === 'last_day') {
            return $nextDate->endOfMonth()->startOfDay();
        }
        
        if ($type === 'weekday') {
            $week = (int) ($config['week'] ?? 1); // 1=first, 2=second, 3=third, 4=fourth, 5=last
            $day = (int) ($config['day'] ?? 1); // 1=Monday, 7=Sunday
            
            return $this->getNthWeekdayOfMonth($nextDate, $week, $day);
        }
        
        // Default: specific date
        $date = (int) ($config['date'] ?? 1);
        $nextDate->day = min($date, $nextDate->daysInMonth);
        
        return $nextDate;
    }

    private function calculateYearlyNextDate(Carbon $baseDate, array $config): Carbon
    {
        $month = (int) ($config['month'] ?? 1);
        $date = (int) ($config['date'] ?? 1);
        
        $nextDate = $baseDate->copy()->addYear();
        $nextDate->month = $month;
        $nextDate->day = min($date, $nextDate->daysInMonth);
        
        return $nextDate;
    }

    private function getNthWeekdayOfMonth(Carbon $date, int $week, int $dayOfWeek): Carbon
    {
        $result = $date->copy()->startOfMonth();
        
        if ($week === 5) {
            // Last occurrence
            $result->endOfMonth();
            while ($result->dayOfWeekIso !== $dayOfWeek) {
                $result->subDay();
            }
            return $result;
        }
        
        // First occurrence of the day in the month
        while ($result->dayOfWeekIso !== $dayOfWeek) {
            $result->addDay();
        }
        
        // Add weeks to get to the nth occurrence
        $result->addWeeks($week - 1);
        
        // Make sure we didn't overflow to next month
        if ($result->month !== $date->month) {
            // Return the last occurrence in the current month
            $result = $date->copy()->endOfMonth();
            while ($result->dayOfWeekIso !== $dayOfWeek) {
                $result->subDay();
            }
        }
        
        return $result;
    }

    /**
     * Update next due date for a schedule after maintenance completion.
     */
    public function updateScheduleAfterCompletion(MaintenanceSchedule $schedule): void
    {
        $schedule->update([
            'last_performed_at' => now(),
            'next_due_date' => $this->calculateNextDueDate($schedule),
        ]);
    }

    /**
     * Consume inventory for work order parts.
     */
    public function consumeInventoryForWorkOrder(WorkOrder $workOrder, array $parts): void
    {
        foreach ($parts as $part) {
            $positionItem = PositionItem::find($part['position_item_id']);
            
            if ($positionItem && $positionItem->quantity >= $part['quantity_used']) {
                // Create work order part record
                WorkOrderPart::create([
                    'work_order_id' => $workOrder->id,
                    'item_id' => $part['item_id'],
                    'quantity_used' => $part['quantity_used'],
                    'warehouse_id' => $part['warehouse_id'],
                    'position_item_id' => $part['position_item_id'],
                ]);

                // Deduct from inventory
                $positionItem->decrement('quantity', $part['quantity_used']);
            }
        }
    }

    /**
     * Generate maintenance report for an asset.
     */
    public function generateMaintenanceReport(int $assetId, Carbon $startDate, Carbon $endDate): array
    {
        $asset = Asset::with(['assetCategory', 'department'])->findOrFail($assetId);
        
        $workOrders = WorkOrder::where('asset_id', $assetId)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->with(['maintenanceType', 'assignedUser', 'parts.item'])
            ->get();

        $totalCost = $workOrders->sum(function ($wo) {
            return $wo->parts->sum(function ($part) {
                return $part->quantity_used * ($part->item->price ?? 0);
            });
        });

        $totalHours = $workOrders->sum('actual_hours');
        $avgHours = $workOrders->avg('actual_hours');

        return [
            'asset' => $asset,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'work_orders' => $workOrders,
            'total_work_orders' => $workOrders->count(),
            'total_cost' => $totalCost,
            'total_hours' => $totalHours,
            'avg_hours_per_wo' => round($avgHours, 2),
            'downtime_hours' => $this->calculateDowntime($assetId, $startDate, $endDate),
        ];
    }

    /**
     * Get overdue maintenance schedules.
     */
    public function getOverdueSchedules(): Collection
    {
        return MaintenanceSchedule::active()
            ->overdue()
            ->with(['asset', 'maintenanceType', 'assignedUser'])
            ->get();
    }

    /**
     * Get upcoming maintenance schedules.
     */
    public function getUpcomingSchedules(int $days = 7): Collection
    {
        return MaintenanceSchedule::active()
            ->upcoming($days)
            ->with(['asset', 'maintenanceType', 'assignedUser'])
            ->get();
    }

    /**
     * Generate work order number.
     */
    private function generateWONumber(): string
    {
        $date = now()->format('ymd');
        $lastWO = WorkOrder::where('wo_number', 'like', "WO-{$date}-%")
            ->orderBy('wo_number', 'desc')
            ->first();

        if ($lastWO) {
            $lastNumber = (int) substr($lastWO->wo_number, -3);
            $newNumber = str_pad((string)($lastNumber + 1), 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "WO-{$date}-{$newNumber}";
    }

    /**
     * Calculate downtime for an asset.
     */
    private function calculateDowntime(int $assetId, Carbon $startDate, Carbon $endDate): float
    {
        // This is a simplified calculation
        // In a real system, you'd track actual downtime periods
        $workOrders = WorkOrder::where('asset_id', $assetId)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->get();

        return $workOrders->sum('actual_hours') ?? 0;
    }

    /**
     * Calculate total hours worked on a work order.
     */
    public function calculateTotalHours(WorkOrder $workOrder): float
    {
        return $workOrder->progressLogs()->sum('hours_worked');
    }

    /**
     * Get work order timeline with all activities.
     */
    public function getWorkOrderTimeline(WorkOrder $workOrder): Collection
    {
        $timeline = collect();

        // Add work order creation
        $timeline->push([
            'type' => 'work_order_created',
            'date' => $workOrder->created_at,
            'user' => $workOrder->requestedBy,
            'description' => 'Work order created',
            'details' => $workOrder->description,
        ]);

        // Add assignment
        if ($workOrder->assigned_at) {
            $timeline->push([
                'type' => 'work_order_assigned',
                'date' => $workOrder->assigned_at,
                'user' => $workOrder->assignedBy,
                'description' => 'Work order assigned',
                'details' => "Assigned to {$workOrder->assignedUser?->name}",
            ]);
        }

        // Add work started
        if ($workOrder->work_started_at) {
            $timeline->push([
                'type' => 'work_started',
                'date' => $workOrder->work_started_at,
                'user' => $workOrder->assignedUser,
                'description' => 'Work started',
                'details' => 'Operator began work on the task',
            ]);
        }

        // Add progress logs
        foreach ($workOrder->progressLogs as $log) {
            $timeline->push([
                'type' => 'progress_log',
                'date' => $log->logged_at,
                'user' => $log->loggedBy,
                'description' => 'Progress logged',
                'details' => "{$log->hours_worked}h - {$log->completion_percentage}% complete",
                'notes' => $log->progress_notes,
            ]);
        }

        // Add actions
        foreach ($workOrder->actions as $action) {
            $timeline->push([
                'type' => 'action_performed',
                'date' => $action->performed_at,
                'user' => $action->performedBy,
                'description' => 'Action performed',
                'details' => ucfirst(str_replace('-', ' ', $action->action_type)),
                'notes' => $action->action_description,
            ]);
        }

        // Add work finished
        if ($workOrder->work_finished_at) {
            $timeline->push([
                'type' => 'work_finished',
                'date' => $workOrder->work_finished_at,
                'user' => $workOrder->assignedUser,
                'description' => 'Work finished',
                'details' => 'Operator completed work and submitted for verification',
            ]);
        }

        // Add verification
        if ($workOrder->verified_at) {
            $timeline->push([
                'type' => 'work_verified',
                'date' => $workOrder->verified_at,
                'user' => $workOrder->verifiedBy,
                'description' => 'Work verified',
                'details' => 'Engineering staff approved the work',
                'notes' => $workOrder->verification_notes,
            ]);
        }

        // Add completion
        if ($workOrder->completed_date) {
            $timeline->push([
                'type' => 'work_completed',
                'date' => $workOrder->completed_date,
                'user' => $workOrder->requestedBy,
                'description' => 'Work order closed',
                'details' => 'Requester closed the work order',
            ]);
        }

        return $timeline->sortBy('date');
    }
}
