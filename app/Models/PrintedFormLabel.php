<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PrintedFormLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_request_id',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function formRequest(): BelongsTo
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeByFormRequest($query, int $requestId)
    {
        return $query->where('form_request_id', $requestId);
    }

    public function scopeByGenerator($query, int $userId)
    {
        return $query->where('generated_by', $userId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }
}
