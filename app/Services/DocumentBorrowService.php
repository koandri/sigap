<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentBorrowStatus;
use App\Models\Document;
use App\Models\DocumentBorrow;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DocumentBorrowService
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService,
        private readonly DocumentService $documentService
    ) {}

    /**
     * Create a borrow request for multiple documents.
     * Auto-approves for Super Admin/Owner roles.
     * 
     * @param User $user
     * @param array $data ['notes' => string, 'documents' => [['document_id' => int, 'due_date' => string|null], ...]]
     * @return DocumentBorrowRequest
     */
    public function createBorrowRequest(User $user, array $data): DocumentBorrowRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $isPrivilegedUser = $user->hasRole(['Super Admin', 'Owner']);

            // Create the parent request
            $request = DocumentBorrowRequest::create([
                'user_id' => $user->id,
                'notes' => $data['notes'] ?? null,
                'status' => $isPrivilegedUser ? DocumentBorrowStatus::Approved : DocumentBorrowStatus::Pending,
                'approved_by' => $isPrivilegedUser ? $user->id : null,
                'approved_at' => $isPrivilegedUser ? now() : null,
            ]);

            // Create borrow items for each document
            foreach ($data['documents'] as $docData) {
                DocumentBorrow::create([
                    'borrow_request_id' => $request->id,
                    'document_id' => $docData['document_id'],
                    'status' => $isPrivilegedUser ? DocumentBorrowStatus::Approved : DocumentBorrowStatus::Pending,
                    'due_date' => $docData['due_date'] ?? null,
                ]);
            }

            // Reload to include items
            $request->load('items.document');

            // Send notifications
            if ($isPrivilegedUser) {
                $this->notifyBorrowApproved($request);
            } else {
                $this->notifyApproversOfNewRequest($request);
            }

            return $request;
        });
    }

    /**
     * Approve a pending borrow request.
     */
    public function approveBorrowRequest(DocumentBorrow $borrow, User $approver): void
    {
        DB::transaction(function () use ($borrow, $approver) {
            $borrow->update([
                'status' => DocumentBorrowStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $this->notifyBorrowApproved($borrow);
        });
    }

    /**
     * Reject a pending borrow request.
     */
    public function rejectBorrowRequest(DocumentBorrow $borrow, User $approver, string $reason): void
    {
        DB::transaction(function () use ($borrow, $approver, $reason) {
            $borrow->update([
                'status' => DocumentBorrowStatus::Rejected,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->notifyBorrowRejected($borrow);
        });
    }

    /**
     * Mark document as checked out (physically collected).
     */
    public function checkoutDocument(DocumentBorrow $borrow): void
    {
        DB::transaction(function () use ($borrow) {
            $borrow->update([
                'status' => DocumentBorrowStatus::CheckedOut,
                'checkout_at' => now(),
            ]);

            $this->notifyDocumentCheckedOut($borrow);
        });
    }

    /**
     * Mark document as returned.
     */
    public function returnDocument(DocumentBorrow $borrow): void
    {
        DB::transaction(function () use ($borrow) {
            $borrow->update([
                'status' => DocumentBorrowStatus::Returned,
                'returned_at' => now(),
            ]);

            $this->notifyDocumentReturned($borrow);
        });
    }

    /**
     * Check if a user can borrow a specific document.
     */
    public function canBorrow(User $user, Document $document): array
    {
        $errors = [];

        // Check if user has digital access to the document
        if (!$this->documentService->checkUserCanAccess($user, $document)) {
            $errors[] = 'You do not have access to this document.';
        }

        // Check if document is available (not currently borrowed)
        if (!$this->isDocumentAvailable($document)) {
            $errors[] = 'This document is currently borrowed by another user.';
        }

        // Check if user already has an active borrow request for this document
        if ($this->userHasActiveBorrow($user, $document)) {
            $errors[] = 'You already have an active borrow request for this document.';
        }

        return [
            'can_borrow' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if the physical document copy is available.
     */
    public function isDocumentAvailable(Document $document): bool
    {
        return !$document->borrows()
            ->whereIn('status', [
                DocumentBorrowStatus::Pending,
                DocumentBorrowStatus::Approved,
                DocumentBorrowStatus::CheckedOut,
            ])
            ->exists();
    }

    /**
     * Check if user has an active borrow for a document.
     */
    public function userHasActiveBorrow(User $user, Document $document): bool
    {
        return DocumentBorrow::where('user_id', $user->id)
            ->where('document_id', $document->id)
            ->whereIn('status', [
                DocumentBorrowStatus::Pending,
                DocumentBorrowStatus::Approved,
                DocumentBorrowStatus::CheckedOut,
            ])
            ->exists();
    }

    /**
     * Get all pending borrow requests (for approvers).
     */
    public function getPendingRequests(): Collection
    {
        return DocumentBorrow::with(['document', 'user'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get user's borrow history.
     */
    public function getUserBorrows(User $user): Collection
    {
        return DocumentBorrow::with(['document', 'approver'])
            ->byUser($user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get currently borrowed documents.
     */
    public function getCurrentlyBorrowedDocuments(): Collection
    {
        return DocumentBorrow::with(['document', 'user'])
            ->checkedOut()
            ->orderBy('checkout_at', 'desc')
            ->get();
    }

    /**
     * Get overdue borrows.
     */
    public function getOverdueBorrows(): Collection
    {
        return DocumentBorrow::with(['document', 'user'])
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get borrows due soon.
     */
    public function getBorrowsDueSoon(int $days = 1): Collection
    {
        return DocumentBorrow::with(['document', 'user'])
            ->dueSoon($days)
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get documents available for borrowing by user.
     */
    public function getAvailableDocumentsForUser(User $user): Collection
    {
        $accessibleDocuments = $this->documentService->getDocumentsAccessibleByUser($user);

        // Filter out documents that are currently borrowed or have pending requests
        return $accessibleDocuments->filter(function ($document) use ($user) {
            return $this->isDocumentAvailable($document) && !$this->userHasActiveBorrow($user, $document);
        });
    }

    /**
     * Get statistics for dashboard.
     */
    public function getStatistics(): array
    {
        return [
            'total_borrowed' => DocumentBorrow::checkedOut()->count(),
            'total_overdue' => DocumentBorrow::overdue()->count(),
            'pending_approvals' => DocumentBorrow::pending()->count(),
            'due_soon' => DocumentBorrow::dueSoon(1)->count(),
        ];
    }

    // ========================================
    // Notification Methods
    // ========================================

    /**
     * Notify approvers of a new borrow request.
     */
    private function notifyApproversOfNewRequest(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user']);

            // Get Super Admin and Owner users
            $approvers = User::role(['Super Admin', 'Owner'])
                ->where('active', true)
                ->whereNotNull('mobilephone_no')
                ->get();

            $message = $this->formatNewRequestMessage($borrow);

            foreach ($approvers as $approver) {
                $chatId = $this->formatPhoneNumber($approver->mobilephone_no);
                if ($chatId) {
                    $this->whatsAppService->sendMessage($chatId, $message);
                }
            }

            Log::info('Borrow request notifications sent', [
                'borrow_id' => $borrow->id,
                'approvers_count' => $approvers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send borrow request notifications', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify requester that their borrow request was approved.
     */
    private function notifyBorrowApproved(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user', 'approver']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatApprovedMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Borrow approval notification sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send borrow approval notification', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify requester that their borrow request was rejected.
     */
    private function notifyBorrowRejected(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user', 'approver']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatRejectedMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Borrow rejection notification sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send borrow rejection notification', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify requester that document has been checked out.
     */
    private function notifyDocumentCheckedOut(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatCheckedOutMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Document checkout notification sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send checkout notification', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify requester that document has been returned.
     */
    private function notifyDocumentReturned(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatReturnedMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Document return notification sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send return notification', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send due date reminder (called from scheduled command).
     */
    public function sendDueDateReminder(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatDueReminderMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Due date reminder sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send due date reminder', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send overdue notice (called from scheduled command).
     */
    public function sendOverdueNotice(DocumentBorrow $borrow): void
    {
        try {
            $borrow->load(['document', 'user']);

            if (!$borrow->user->mobilephone_no) {
                return;
            }

            $message = $this->formatOverdueMessage($borrow);
            $chatId = $this->formatPhoneNumber($borrow->user->mobilephone_no);

            if ($chatId) {
                $this->whatsAppService->sendMessage($chatId, $message);
            }

            Log::info('Overdue notice sent', ['borrow_id' => $borrow->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send overdue notice', [
                'borrow_id' => $borrow->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ========================================
    // Message Formatting Methods
    // ========================================

    private function formatNewRequestMessage(DocumentBorrow $borrow): string
    {
        $dueDate = $borrow->due_date ? $borrow->due_date->format('d M Y') : 'No due date';

        return "📚 *New Document Borrow Request*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Requested by: {$borrow->user->name}\n" .
            "Due Date: {$dueDate}\n" .
            ($borrow->notes ? "Notes: {$borrow->notes}\n" : '') .
            "\nPlease review this request in SIGAP.";
    }

    private function formatApprovedMessage(DocumentBorrow $borrow): string
    {
        $dueDate = $borrow->due_date ? $borrow->due_date->format('d M Y') : 'No due date';

        return "✅ *Document Borrow Request Approved*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Due Date: {$dueDate}\n" .
            "Approved by: {$borrow->approver->name}\n" .
            "\nYou can now collect the document from Document Control.";
    }

    private function formatRejectedMessage(DocumentBorrow $borrow): string
    {
        return "❌ *Document Borrow Request Rejected*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Rejected by: {$borrow->approver->name}\n" .
            "Reason: {$borrow->rejection_reason}";
    }

    private function formatCheckedOutMessage(DocumentBorrow $borrow): string
    {
        $dueDate = $borrow->due_date ? $borrow->due_date->format('d M Y') : 'No due date';

        return "📖 *Document Checked Out*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Checkout Date: " . now()->format('d M Y H:i') . "\n" .
            "Due Date: {$dueDate}\n" .
            "\nPlease return the document by the due date.";
    }

    private function formatReturnedMessage(DocumentBorrow $borrow): string
    {
        return "✅ *Document Returned Successfully*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Returned Date: " . now()->format('d M Y H:i') . "\n" .
            "\nThank you for returning the document.";
    }

    private function formatDueReminderMessage(DocumentBorrow $borrow): string
    {
        return "⏰ *Document Return Reminder*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Due Date: " . $borrow->due_date->format('d M Y') . "\n" .
            "\nPlease remember to return the document by the due date.";
    }

    private function formatOverdueMessage(DocumentBorrow $borrow): string
    {
        $daysOverdue = $borrow->days_overdue;

        return "⚠️ *OVERDUE: Document Return Required*\n\n" .
            "Document: {$borrow->document->title}\n" .
            "Doc Number: {$borrow->document->document_number}\n" .
            "Due Date: " . $borrow->due_date->format('d M Y') . "\n" .
            "Days Overdue: {$daysOverdue}\n" .
            "\nPlease return the document immediately.";
    }

    /**
     * Format phone number to WhatsApp chat ID format.
     */
    private function formatPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Handle Indonesian numbers
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone . '@c.us';
    }
}

