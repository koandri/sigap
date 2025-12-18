<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AccessType;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DocumentAccessService
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {}

    public function createAccessRequest(DocumentVersion $version, User $user, array $data): DocumentAccessRequest
    {
        return DB::transaction(function () use ($version, $user, $data) {
            $request = DocumentAccessRequest::create([
                'document_version_id' => $version->id,
                'user_id' => $user->id,
                'access_type' => $data['access_type'],
                'requested_expiry_date' => $data['requested_expiry_date'] ?? null,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // Notify approvers
            $this->notifyApprovers($request);

            return $request;
        });
    }

    public function approveAccessRequest(DocumentAccessRequest $request, User $approver, array $modifications = []): void
    {
        DB::transaction(function () use ($request, $approver, $modifications) {
            $request->update([
                'approved_by' => $approver->id,
                'approved_access_type' => $modifications['access_type'] ?? $request->access_type,
                'approved_expiry_date' => $modifications['expiry_date'] ?? $request->requested_expiry_date,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Notify requester
            $this->notifyRequester($request, 'approved');
        });
    }

    public function rejectAccessRequest(DocumentAccessRequest $request, User $approver, string $reason): void
    {
        DB::transaction(function () use ($request, $approver, $reason) {
            $request->update([
                'approved_by' => $approver->id,
                'status' => 'rejected',
                'approved_at' => now(),
            ]);

            // Notify requester
            $this->notifyRequester($request, 'rejected', $reason);
        });
    }

    public function checkAccess(User $user, DocumentVersion $version): bool
    {
        // Super Admin, Owner, and Document Control always have access
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return true;
        }

        // Check for active access request
        $accessRequest = $user->documentAccessRequests()
            ->where('document_version_id', $version->id)
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('approved_expiry_date')
                      ->orWhere('approved_expiry_date', '>', now());
            })
            ->first();

        if (!$accessRequest) {
            return false;
        }

        // For one-time access, check if already used
        if ($accessRequest->getEffectiveAccessType()->isOneTime()) {
            return !$this->hasUsedOneTimeAccess($accessRequest);
        }

        return true;
    }

    public function logAccess(User $user, DocumentVersion $version, string $ipAddress): void
    {
        $accessRequest = $user->documentAccessRequests()
            ->where('document_version_id', $version->id)
            ->where('status', 'approved')
            ->first();

        if ($accessRequest) {
            $accessRequest->accessLogs()->create([
                'user_id' => $user->id,
                'document_version_id' => $version->id,
                'accessed_at' => now(),
                'ip_address' => $ipAddress,
            ]);
        }
    }

    public function getUserAccessibleDocuments(User $user): Collection
    {
        // Super Admin, Owner, and Document Control can see all active document versions
        if ($user->hasRole(['Super Admin', 'Owner', 'Document Control'])) {
            return DocumentVersion::with(['document', 'document.department', 'accessRequests'])
                ->whereHas('document')
                ->get();
        }

        $query = DocumentVersion::with(['document', 'document.department', 'accessRequests'])
            ->whereHas('accessRequests', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'approved')
                  ->where(function ($subQ) {
                      $subQ->whereNull('approved_expiry_date')
                           ->orWhere('approved_expiry_date', '>', now());
                  });
            });

        // Filter out one-time access that has been used
        return $query->get()->filter(function ($version) use ($user) {
            $accessRequest = $user->documentAccessRequests()
                ->where('document_version_id', $version->id)
                ->where('status', 'approved')
                ->first();

            if (!$accessRequest) {
                return false;
            }

            if ($accessRequest->getEffectiveAccessType()->isOneTime()) {
                return !$this->hasUsedOneTimeAccess($accessRequest);
            }

            return true;
        });
    }

    public function revokeExpiredAccess(): int
    {
        $expiredRequests = DocumentAccessRequest::where('status', 'approved')
            ->where('approved_expiry_date', '<', now())
            ->get();

        $count = $expiredRequests->count();

        foreach ($expiredRequests as $request) {
            $request->update(['status' => 'expired']);
        }

        return $count;
    }

    public function getPendingRequests(): Collection
    {
        return DocumentAccessRequest::with(['user', 'documentVersion.document'])
            ->where('status', 'pending')
            ->orderBy('requested_at')
            ->get();
    }

    public function getPendingRequestsForApprover(User $approver): Collection
    {
        return $this->getPendingRequests()->filter(function ($request) use ($approver) {
            return $approver->hasRole(['Super Admin', 'Owner']);
        });
    }

    private function hasUsedOneTimeAccess(DocumentAccessRequest $request): bool
    {
        return $request->accessLogs()->exists();
    }

    private function notifyApprovers(DocumentAccessRequest $request): void
    {
        // Get Super Admin and Owner users
        $approvers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Super Admin', 'Owner']);
        })->get();

        $message = "📄 *Document Access Request*\n\n";
        $message .= "Document: *{$request->documentVersion->document->title}*\n";
        $message .= "Requested by: {$request->user->name}\n";
        $message .= "Access type: {$request->access_type}\n";
        
        if ($request->requested_expiry_date) {
            $message .= "Requested expiry: " . $request->requested_expiry_date->format('d M Y') . "\n";
        }
        
        $message .= "\nPlease review: " . route('document-access-requests.pending');

        foreach ($approvers as $approver) {
            $this->sendNotificationToUser($approver, $message, 'Document Access Request');
        }
    }

    private function notifyRequester(DocumentAccessRequest $request, string $status, string $reason = null): void
    {
        if ($status === 'approved') {
            $message = "✅ *Document Access Approved*\n\n";
            $message .= "Document: *{$request->documentVersion->document->title}*\n";
            $message .= "Access type: {$request->getEffectiveAccessType()->label()}\n";
            
            if ($request->approved_expiry_date) {
                $message .= "Expiry date: " . $request->approved_expiry_date->format('d M Y') . "\n";
            }
            
            $message .= "\nView document: " . route('documents.show', $request->documentVersion->document);
        } else {
            $message = "❌ *Document Access Rejected*\n\n";
            $message .= "Document: *{$request->documentVersion->document->title}*\n";
            
            if ($reason) {
                $message .= "Reason: {$reason}\n";
            }
        }

        $this->sendNotificationToUser($request->user, $message, 'Document Access Processed');
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
