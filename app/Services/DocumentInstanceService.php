<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentInstanceStatus;
use App\Models\DocumentInstance;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DocumentInstanceService
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {}
    public function createInstance(DocumentVersion $templateVersion, User $user, array $data): DocumentInstance
    {
        return DB::transaction(function () use ($templateVersion, $user, $data) {
            $instanceNumber = $this->generateInstanceNumber($templateVersion->document->document_type->value);
            
            return DocumentInstance::create([
                'template_document_version_id' => $templateVersion->id,
                'instance_number' => $instanceNumber,
                'subject' => $data['subject'],
                'content_summary' => $data['content_summary'] ?? null,
                'created_by' => $user->id,
                'status' => DocumentInstanceStatus::Draft,
            ]);
        });
    }

    public function updateInstance(DocumentInstance $instance, array $data): DocumentInstance
    {
        return DB::transaction(function () use ($instance, $data) {
            $instance->update($data);
            return $instance->fresh();
        });
    }

    public function submitForApproval(DocumentInstance $instance): void
    {
        DB::transaction(function () use ($instance) {
            $instance->update([
                'status' => DocumentInstanceStatus::PendingApproval,
            ]);
            
            // Reload instance with relationships
            $instance->load(['creator', 'templateVersion.document']);
        });
        
        // Notify approvers after transaction
        $this->notifyApprovers($instance);
    }

    public function approveInstance(DocumentInstance $instance, User $approver, ?string $finalPdfPath = null): void
    {
        DB::transaction(function () use ($instance, $approver, $finalPdfPath) {
            $instance->update([
                'status' => DocumentInstanceStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'final_pdf_path' => $finalPdfPath,
            ]);
        });
    }

    public function rejectInstance(DocumentInstance $instance): void
    {
        DB::transaction(function () use ($instance) {
            $instance->update([
                'status' => DocumentInstanceStatus::Draft,
            ]);
        });
    }

    private function generateInstanceNumber(string $documentType): string
    {
        $prefix = match ($documentType) {
            'internal_memo' => 'IM',
            'outgoing_letter' => 'OL',
            default => 'INST',
        };
        
        $date = now()->format('ymd');
        $prefix = "{$prefix}-{$date}-";
        
        // Get the last instance number for today
        $lastInstance = DocumentInstance::where('instance_number', 'like', $prefix . '%')
            ->orderBy('instance_number', 'desc')
            ->first();
        
        if ($lastInstance) {
            $lastNumber = (int) substr($lastInstance->instance_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Notify approvers when correspondence is submitted for approval
     */
    private function notifyApprovers(DocumentInstance $instance): void
    {
        $approvers = $this->getApprovers($instance);
        
        if ($approvers->isEmpty()) {
            Log::warning("No approvers found for correspondence instance", [
                'instance_id' => $instance->id,
                'instance_number' => $instance->instance_number,
            ]);
            return;
        }

        $message = "📧 *Correspondence Approval Request*\n\n";
        $message .= "Type: *{$instance->templateVersion->document->document_type->label()}*\n";
        $message .= "Reference: {$instance->instance_number}\n";
        $message .= "Subject: {$instance->subject}\n";
        $message .= "Submitted by: {$instance->creator->name}\n";
        
        if ($instance->content_summary) {
            $message .= "Summary: {$instance->content_summary}\n";
        }
        
        $message .= "\nPlease review: " . route('correspondences.show', $instance);

        foreach ($approvers as $approver) {
            $this->sendNotificationToUser($approver, $message, 'Correspondence Approval Request');
        }
    }

    /**
     * Get list of approvers for a correspondence instance
     */
    private function getApprovers(DocumentInstance $instance): \Illuminate\Database\Eloquent\Collection
    {
        $approverIds = collect();
        
        // Get Super Admin and Owner users (can always approve)
        $superAdmins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Super Admin', 'Owner']);
        })->get();
        
        foreach ($superAdmins as $admin) {
            $approverIds->push($admin->id);
        }
        
        // Get creator's manager (if exists and has permission)
        $creator = $instance->creator;
        if ($creator && $creator->manager_id) {
            $manager = User::find($creator->manager_id);
            if ($manager && $manager->hasPermissionTo('dms.instances.approve')) {
                if (!$approverIds->contains($manager->id)) {
                    $approverIds->push($manager->id);
                }
            }
        }
        
        // Get users with dms.instances.approve permission
        $permissionApprovers = User::permission('dms.instances.approve')->get();
        foreach ($permissionApprovers as $approver) {
            if (!$approverIds->contains($approver->id)) {
                $approverIds->push($approver->id);
            }
        }
        
        // Return unique users
        return User::whereIn('id', $approverIds->unique()->values())->get();
    }

    /**
     * Send notification to user via WhatsApp, fallback to Pushover on failure.
     */
    private function sendNotificationToUser(User $user, string $message, string $notificationType): bool
    {
        // Check if user has mobile phone number
        if (empty($user->mobilephone_no)) {
            Log::warning("User has no mobile phone number for WhatsApp notification", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'notification_type' => $notificationType,
            ]);
            
            // Send failure notification via Pushover
            $this->pushoverService->sendWhatsAppFailureNotification(
                $notificationType,
                $user->name . ' (No Phone)',
                $message
            );
            
            return false;
        }

        // Format WhatsApp chat ID (phone number + @c.us)
        $chatId = validateMobileNumber($user->mobilephone_no);

        // Try to send via WhatsApp
        $whatsAppSuccess = $this->whatsAppService->sendMessage($chatId, $message);

        if (!$whatsAppSuccess) {
            // WhatsApp failed, send notification via Pushover
            Log::warning("WhatsApp notification failed, sending Pushover fallback", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'chat_id' => $chatId,
                'notification_type' => $notificationType,
            ]);

            $this->pushoverService->sendWhatsAppFailureNotification(
                $notificationType,
                $user->name . ' (' . $user->mobilephone_no . ')',
                $message
            );

            return false;
        }

        return true;
    }
}

