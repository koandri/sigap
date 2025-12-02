<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentBorrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DocumentBorrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'status',
        'due_date',
        'checkout_at',
        'returned_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'status' => DocumentBorrowStatus::class,
        'due_date' => 'datetime',
        'checkout_at' => 'datetime',
        'returned_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', DocumentBorrowStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', DocumentBorrowStatus::Approved);
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', DocumentBorrowStatus::CheckedOut);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', DocumentBorrowStatus::Returned);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', DocumentBorrowStatus::Rejected);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            DocumentBorrowStatus::Pending,
            DocumentBorrowStatus::Approved,
            DocumentBorrowStatus::CheckedOut,
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', DocumentBorrowStatus::CheckedOut)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeDueSoon($query, int $days = 1)
    {
        return $query->where('status', DocumentBorrowStatus::CheckedOut)
            ->whereNotNull('due_date')
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays($days));
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    // Accessors

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== DocumentBorrowStatus::CheckedOut) {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }

        return (int) $this->due_date->diffInDays(now());
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if ($this->status !== DocumentBorrowStatus::CheckedOut) {
            return null;
        }

        if (!$this->due_date) {
            return null;
        }

        if ($this->due_date->isPast()) {
            return null;
        }

        return (int) now()->diffInDays($this->due_date);
    }

    // Helper Methods

    public function isPending(): bool
    {
        return $this->status === DocumentBorrowStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === DocumentBorrowStatus::Approved;
    }

    public function isCheckedOut(): bool
    {
        return $this->status === DocumentBorrowStatus::CheckedOut;
    }

    public function isReturned(): bool
    {
        return $this->status === DocumentBorrowStatus::Returned;
    }

    public function isRejected(): bool
    {
        return $this->status === DocumentBorrowStatus::Rejected;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canBeApproved(): bool
    {
        return $this->status->canBeApproved();
    }

    public function canBeCheckedOut(): bool
    {
        return $this->status->canBeCheckedOut();
    }

    public function canBeReturned(): bool
    {
        return $this->status->canBeReturned();
    }
}

