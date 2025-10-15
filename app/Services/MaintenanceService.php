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
            // Check if there's already a pending work order for this schedule
            $existingWO = WorkOrder::where('asset_id', $schedule->asset_id)
                ->where('maintenance_type_id', $schedule->maintenance_type_id)
                ->whereIn('status', ['pending', 'in-progress'])
                ->first();

            if (!$existingWO) {
                $workOrder = WorkOrder::create([
                    'wo_number' => $this->generateWONumber(),
                    'asset_id' => $schedule->asset_id,
                    'maintenance_type_id' => $schedule->maintenance_type_id,
                    'priority' => 'medium',
                    'status' => 'pending',
                    'scheduled_date' => now(),
                    'assigned_to' => $schedule->assigned_to,
                    'requested_by' => auth()->user()?->id,
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
        $baseDate = $schedule->last_performed_at ?? $schedule->created_at;
        return $baseDate->addDays($schedule->frequency_days);
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
}
