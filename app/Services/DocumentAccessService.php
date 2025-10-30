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

final class DocumentAccessService
{
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
        // Super Admin and Owner always have access
        if ($user->hasRole(['Super Admin', 'Owner'])) {
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
        // Super Admin and Owner can see all active document versions
        if ($user->hasRole(['Super Admin', 'Owner'])) {
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

        foreach ($approvers as $approver) {
            // Send notification (implement based on your notification system)
            // $approver->notify(new DocumentAccessRequested($request));
        }
    }

    private function notifyRequester(DocumentAccessRequest $request, string $status, string $reason = null): void
    {
        // Send notification to requester
        // $request->user->notify(new DocumentAccessProcessed($request, $status, $reason));
    }
}
