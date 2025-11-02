<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentVersionStatus;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class DocumentVersionService
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly PushoverService $pushoverService
    ) {}
    public function createVersion(Document $document, array $data): DocumentVersion
    {
        return DB::transaction(function () use ($document, $data) {
            $version = $document->versions()->create($data);
            
            // All versions start as draft and require approval
            // No automatic activation for first version
            
            return $version;
        });
    }

    public function createVersionFromScratch(Document $document, string $fileType): DocumentVersion
    {
        $versionNumber = $this->generateNextVersionNumber($document);
        
        return $this->createVersion($document, [
            'version_number' => $versionNumber,
            'file_path' => '', // Will be set by OnlyOffice
            'file_type' => $fileType,
            'status' => DocumentVersionStatus::Draft,
            'created_by' => Auth::id(),
            'revision_description' => 'Created from scratch',
        ]);
    }

    public function createVersionFromUpload(Document $document, string $filePath, string $fileType): DocumentVersion
    {
        $versionNumber = $this->generateNextVersionNumber($document);
        
        return $this->createVersion($document, [
            'version_number' => $versionNumber,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'status' => DocumentVersionStatus::Draft,
            'created_by' => Auth::id(),
            'revision_description' => 'Uploaded from file',
        ]);
    }

    public function createVersionFromCopy(Document $document, DocumentVersion $sourceVersion): DocumentVersion
    {
        $versionNumber = $this->generateNextVersionNumber($document);
        
        // Copy the file
        $newFilePath = $this->copyFile($document->id, $sourceVersion->file_path);
        
        return $this->createVersion($document, [
            'version_number' => $versionNumber,
            'file_path' => $newFilePath,
            'file_type' => $sourceVersion->file_type,
            'status' => DocumentVersionStatus::Draft,
            'created_by' => Auth::id(),
            'revision_description' => "Copied from version {$sourceVersion->version_number}",
        ]);
    }

    public function submitForApproval(DocumentVersion $version): void
    {
        DB::transaction(function () use ($version) {
            $creator = $version->creator;
            
            // Check if creator has a manager assigned
            if ($creator->manager_id) {
                // Create manager approval request
                $version->approvals()->create([
                    'approver_id' => $creator->manager_id,
                    'approval_tier' => 'manager',
                    'status' => 'pending',
                ]);

                // Update version status
                $version->update([
                    'status' => DocumentVersionStatus::PendingManagerApproval,
                ]);
            } else {
                // No manager assigned, skip to management representative
                $managementRep = $this->getManagementRepresentative();
                
                if (!$managementRep) {
                    throw new \Exception('No manager or management representative found for approval. Please contact an administrator.');
                }
                
                $version->approvals()->create([
                    'approver_id' => $managementRep->id,
                    'approval_tier' => 'management_representative',
                    'status' => 'pending',
                ]);

                // Update version status
                $version->update([
                    'status' => DocumentVersionStatus::PendingMgmtApproval,
                ]);
            }
        });

        // Reload version with relationships and notify approvers
        $version->load(['creator', 'document', 'approvals.approver']);
        $this->notifyApprovers($version);
    }

    public function approveVersion(DocumentVersion $version, User $approver, ?string $notes = null): void
    {
        $wasActivated = false;
        
        DB::transaction(function () use ($version, $approver, $notes, &$wasActivated) {
            // Update approval record
            $approval = $version->approvals()
                ->where('approver_id', $approver->id)
                ->where('status', 'pending')
                ->first();

            if ($approval) {
                $approval->update([
                    'status' => 'approved',
                    'notes' => $notes,
                    'approved_at' => now(),
                ]);
            }

            // Check if all approvals are complete
            if ($this->allApprovalsComplete($version)) {
                $this->activateVersion($version);
                $wasActivated = true;
            } else {
                // Move to next approval tier
                $this->moveToNextApprovalTier($version);
            }
        });

        // Reload version with relationships and notify creator
        $version->load(['creator', 'document']);
        
        if ($wasActivated) {
            $this->notifyCreatorApproved($version);
        }
    }

    public function rejectVersion(DocumentVersion $version, User $approver, string $notes): void
    {
        DB::transaction(function () use ($version, $approver, $notes) {
            // Update approval record
            $approval = $version->approvals()
                ->where('approver_id', $approver->id)
                ->where('status', 'pending')
                ->first();

            if ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'notes' => $notes,
                    'approved_at' => now(),
                ]);
            }

            // Update version status back to draft
            $version->update([
                'status' => DocumentVersionStatus::Draft,
            ]);
        });

        // Reload version with relationships and notify creator
        $version->load(['creator', 'document']);
        $this->notifyCreatorRejected($version, $notes);
    }

    public function activateVersion(DocumentVersion $version): void
    {
        DB::transaction(function () use ($version) {
            // Supersede current active version
            $version->document->versions()
                ->where('status', DocumentVersionStatus::Active)
                ->update(['status' => DocumentVersionStatus::Superseded]);

            // Activate new version
            $version->update([
                'status' => DocumentVersionStatus::Active,
                'finalized_at' => now(),
            ]);
        });
    }

    public function canUserEdit(DocumentVersion $version, User $user): bool
    {
        return $version->isDraft() && 
               $version->created_by === $user->id &&
               $version->document->document_type->canHaveVersions();
    }

    public function checkAccess(User $user, DocumentVersion $version): bool
    {
        // Check if user has direct access to the document
        if ($version->document->accessibleDepartments()->where('department_id', $user->department_id)->exists()) {
            return true;
        }

        // Check if user has been granted specific access to this document version
        if ($version->accessRequests()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists()) {
            return true;
        }

        // Check if user is the creator of the document
        if ($version->document->created_by === $user->id) {
            return true;
        }

        // Check if user has admin/management role
        if ($user->hasRole(['Super Admin', 'IT Staff', 'Owner'])) {
            return true;
        }

        return false;
    }

    public function generateOnlyOfficeConfig(DocumentVersion $version): array
    {
        $documentServerUrl = config('dms.onlyoffice.server_url', 'https://office.suryagroup.app');
        $callbackUrl = route('document-versions.onlyoffice-callback', $version);
        
        return [
            'document' => [
                'fileType' => $version->file_type,
                'key' => $version->id . '_' . time(),
                'title' => $version->document->title,
                'url' => Storage::disk('s3')->url($version->file_path),
            ],
            'documentType' => $this->getOnlyOfficeDocumentType($version->file_type),
            'editorConfig' => [
                'mode' => $this->canUserEdit($version, Auth::user()) ? 'edit' : 'view',
                'lang' => 'en',
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                ],
            ],
            'height' => '100%',
            'width' => '100%',
        ];
    }

    public function handleOnlyOfficeCallback(DocumentVersion $version, array $callbackData): void
    {
        if (isset($callbackData['status']) && $callbackData['status'] === 2) {
            // Document is ready for saving
            $this->saveDocumentFromOnlyOffice($version, $callbackData);
        }
    }

    private function generateNextVersionNumber(Document $document): int
    {
        $latestVersion = $document->versions()
            ->orderByRaw('CAST(version_number AS UNSIGNED) DESC')
            ->first();

        if (!$latestVersion) {
            return 0;
        }

        return (int) $latestVersion->version_number + 1;
    }

    private function copyFile(int $documentId, string $sourcePath): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $newPath = 'documents/versions/' . $documentId . '/' . $filename;
        
        Storage::disk('s3')->copy($sourcePath, $newPath);
        
        return $newPath;
    }

    private function allApprovalsComplete(DocumentVersion $version): bool
    {
        return $version->approvals()
            ->where('status', 'pending')
            ->count() === 0;
    }

    private function moveToNextApprovalTier(DocumentVersion $version): void
    {
        $version->update([
            'status' => DocumentVersionStatus::PendingMgmtApproval,
        ]);

        // Create management representative approval request
        $managementRep = $this->getManagementRepresentative();
        if ($managementRep) {
            $version->approvals()->create([
                'approver_id' => $managementRep->id,
                'approval_tier' => 'management_representative',
                'status' => 'pending',
            ]);

            // Reload and notify new approver
            $version->load(['creator', 'document', 'approvals.approver']);
            $this->notifyApprovers($version);
        }
    }

    private function getManagementRepresentative(): ?User
    {
        // This should be configured in the system
        // For now, return the first active Super Admin or Owner
        return User::where('active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Super Admin', 'Owner']);
            })
            ->first();
    }

    private function getOnlyOfficeDocumentType(string $fileType): string
    {
        return match ($fileType) {
            'docx' => 'word',
            'xlsx' => 'cell',
            'pptx' => 'slide',
            default => 'word',
        };
    }

    private function saveDocumentFromOnlyOffice(DocumentVersion $version, array $callbackData): void
    {
        // This would typically involve downloading the file from OnlyOffice
        // and saving it to S3. Implementation depends on OnlyOffice callback format.
        // For now, we'll just update the file path if provided.
        if (isset($callbackData['url'])) {
            $version->update([
                'file_path' => $callbackData['url'],
            ]);
        }
    }

    /**
     * Notify approvers when document version is submitted for approval
     */
    private function notifyApprovers(DocumentVersion $version): void
    {
        $approvers = collect();
        $approverIds = [];
        
        // Get pending approvers from approvals
        foreach ($version->approvals()->where('status', 'pending')->get() as $approval) {
            $approver = $approval->approver;
            if ($approver && !in_array($approver->id, $approverIds)) {
                $approvers->push($approver);
                $approverIds[] = $approver->id;
            }
        }

        if ($approvers->isEmpty()) {
            Log::warning("No approvers found for document version", [
                'version_id' => $version->id,
                'document_title' => $version->document->title,
            ]);
            return;
        }

        $message = "📄 *Document Version Approval Request*\n\n";
        $message .= "Document: *{$version->document->title}*\n";
        $message .= "Version: {$version->version_number}\n";
        $message .= "Submitted by: {$version->creator->name}\n";
        
        if ($version->revision_description) {
            $message .= "Revision: {$version->revision_description}\n";
        }
        
        $message .= "\nPlease review: " . route('document-approvals.index');

        foreach ($approvers as $approver) {
            $this->sendNotificationToUser($approver, $message, 'Document Version Approval Request');
        }
    }

    /**
     * Notify creator when document version is approved and activated
     */
    private function notifyCreatorApproved(DocumentVersion $version): void
    {
        $creator = $version->creator;
        
        if (!$creator) {
            return;
        }

        $message = "✅ *Document Version Approved*\n\n";
        $message .= "Document: *{$version->document->title}*\n";
        $message .= "Version: {$version->version_number}\n";
        $message .= "\nThe document version has been approved and is now active.\n";
        $message .= "\nView document: " . route('documents.show', $version->document);

        $this->sendNotificationToUser($creator, $message, 'Document Version Approved');
    }

    /**
     * Notify creator when document version is rejected
     */
    private function notifyCreatorRejected(DocumentVersion $version, string $notes): void
    {
        $creator = $version->creator;
        
        if (!$creator) {
            return;
        }

        $message = "❌ *Document Version Rejected*\n\n";
        $message .= "Document: *{$version->document->title}*\n";
        $message .= "Version: {$version->version_number}\n";
        $message .= "Reason: {$notes}\n";
        $message .= "\nPlease review and resubmit after making corrections.\n";
        $message .= "\nView document: " . route('documents.show', $version->document);

        $this->sendNotificationToUser($creator, $message, 'Document Version Rejected');
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
