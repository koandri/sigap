<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FormRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FormRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'request_date',
        'acknowledged_at',
        'acknowledged_by',
        'ready_at',
        'collected_at',
        'status',
    ];

    protected $casts = [
        'status' => FormRequestStatus::class,
        'request_date' => 'datetime',
        'acknowledged_at' => 'datetime',
        'ready_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormRequestItem::class);
    }

    public function printedFormLabels(): HasMany
    {
        return $this->hasMany(PrintedFormLabel::class);
    }

    public function scopeByRequester($query, int $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', FormRequestStatus::Pending);
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', FormRequestStatus::Acknowledged);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', FormRequestStatus::Processing);
    }

    public function scopeReady($query)
    {
        return $query->where('status', FormRequestStatus::Ready);
    }

    public function scopeCollected($query)
    {
        return $query->where('status', FormRequestStatus::Collected);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', FormRequestStatus::Completed);
    }

    public function isPending(): bool
    {
        return $this->status === FormRequestStatus::Pending;
    }

    public function isAcknowledged(): bool
    {
        return $this->status === FormRequestStatus::Acknowledged;
    }

    public function isProcessing(): bool
    {
        return $this->status === FormRequestStatus::Processing;
    }

    public function isReady(): bool
    {
        return $this->status === FormRequestStatus::Ready;
    }

    public function isCollected(): bool
    {
        return $this->status === FormRequestStatus::Collected;
    }

    public function isCompleted(): bool
    {
        return $this->status === FormRequestStatus::Completed;
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getTotalFormsAttribute(): int
    {
        return $this->items->count();
    }
}
