<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AssetDisposalService
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {}
    /**
     * Mark asset as disposed and handle related cleanup.
     */
    public function disposeAsset(
        Asset $asset,
        string $reason,
        User $disposedBy,
        ?WorkOrder $workOrder = null
    ): array {
        try {
            DB::beginTransaction();
            
            // Get active schedules before deactivating
            $activeSchedules = $asset->maintenanceSchedules()
                ->where('is_active', true)
                ->with(['maintenanceType', 'assignedUser'])
                ->get();
            
            $deactivatedSchedulesCount = $activeSchedules->count();
            $deactivatedSchedulesData = [];
            
            // Deactivate all maintenance schedules
            foreach ($activeSchedules as $schedule) {
                $schedule->update([
                    'is_active' => false,
                    'description' => $schedule->description . " [DEACTIVATED: Asset disposed on " . now()->format('Y-m-d') . "]"
                ]);
                
                $deactivatedSchedulesData[] = [
                    'maintenance_type' => $schedule->maintenanceType->name,
                    'frequency' => $schedule->frequency_description,
                    'assigned_to' => $schedule->assignedUser?->name,
                ];
            }
            
            // Update asset
            $asset->update([
                'status' => 'disposed',
                'is_active' => false,
                'disposed_date' => now(),
                'disposal_reason' => $reason,
                'disposed_by' => $disposedBy->id,
                'disposal_work_order_id' => $workOrder?->id,
            ]);
            
            DB::commit();
            
            // Send notifications to Engineering Staff
            if ($workOrder) {
                $this->notifyEngineeringStaff($asset, $workOrder, $deactivatedSchedulesCount, $deactivatedSchedulesData);
            }
            
            return [
                'success' => true,
                'deactivated_schedules' => $deactivatedSchedulesCount,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Asset disposal failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Notify Engineering Staff about asset disposal.
     */
    private function notifyEngineeringStaff(
        Asset $asset,
        WorkOrder $workOrder,
        int $deactivatedSchedulesCount,
        array $deactivatedSchedules
    ): void {
        try {
            // Send WhatsApp notification
            $this->sendWhatsAppNotification($asset, $workOrder, $deactivatedSchedulesCount);
            
        } catch (\Exception $e) {
            Log::error("Failed to notify Engineering Staff: " . $e->getMessage());
            // Don't throw - notifications are supplementary
        }
    }
    
    /**
     * Send WhatsApp notification.
     */
    private function sendWhatsAppNotification(
        Asset $asset,
        WorkOrder $workOrder,
        int $deactivatedSchedulesCount
    ): void {
        try {
            $message = "🚨 *Asset Disposal Alert*\n\n";
            $message .= "Asset: *{$asset->name}* ({$asset->code})\n";
            $message .= "WO: {$workOrder->wo_number}\n";
            $message .= "Disposal Reason: {$asset->disposal_reason}\n\n";
            
            if ($deactivatedSchedulesCount > 0) {
                $message .= "⚠️ {$deactivatedSchedulesCount} maintenance schedule(s) have been automatically deactivated.\n\n";
            }
            
            $message .= "Please review: " . route('maintenance.assets.show', $asset);
            
            $groupId = env('ENGINEERING_WHATSAPP_GROUP');
            
            if (!$groupId) {
                Log::warning('ENGINEERING_WHATSAPP_GROUP not configured');
                return;
            }
            
            $success = $this->whatsAppService->sendMessage($groupId, $message);
            
            // If WhatsApp fails, send Pushover notification
            if (!$success) {
                $this->pushoverService->sendWhatsAppFailureNotification(
                    'Asset Disposal Alert',
                    $groupId,
                    $message
                );
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
        }
    }
}

