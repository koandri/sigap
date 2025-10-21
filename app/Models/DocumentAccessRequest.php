<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DocumentAccessRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_version_id',
        'user_id',
        'access_type',
        'requested_expiry_date',
        'approved_by',
        'approved_access_type',
        'approved_expiry_date',
        'status',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'access_type' => AccessType::class,
        'approved_access_type' => AccessType::class,
        'requested_expiry_date' => 'datetime',
        'approved_expiry_date' => 'datetime',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('approved_expiry_date')
                  ->orWhere('approved_expiry_date', '>', now());
            });
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        if (!$this->approved_expiry_date) {
            return true;
        }

        return $this->approved_expiry_date->isFuture();
    }

    public function isExpired(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        if (!$this->approved_expiry_date) {
            return false;
        }

        return $this->approved_expiry_date->isPast();
    }

    public function getEffectiveAccessType(): AccessType
    {
        return $this->approved_access_type ?? $this->access_type;
    }

    public function getEffectiveExpiryDate(): ?\DateTime
    {
        return $this->approved_expiry_date ?? $this->requested_expiry_date;
    }
}
