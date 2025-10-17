<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CleaningRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'request_number',
        'requester_name',
        'requester_phone',
        'requester_user_id',
        'location_id',
        'request_type',
        'description',
        'photo',
        'status',
        'handled_by',
        'handled_at',
        'handling_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'handled_at' => 'datetime',
    ];

    /**
     * Get the location for this request.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the requester user (if authenticated).
     */
    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    /**
     * Get the user who handled this request.
     */
    public function handledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Scope to filter pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter by request type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Check if this is a cleaning request.
     */
    public function isCleaningRequest(): bool
    {
        return $this->request_type === 'cleaning';
    }

    /**
     * Check if this is a repair request.
     */
    public function isRepairRequest(): bool
    {
        return $this->request_type === 'repair';
    }
}
