<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DocumentAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_access_request_id',
        'user_id',
        'document_version_id',
        'accessed_at',
        'ip_address',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    public function accessRequest(): BelongsTo
    {
        return $this->belongsTo(DocumentAccessRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDocumentVersion($query, int $versionId)
    {
        return $query->where('document_version_id', $versionId);
    }

    public function scopeByAccessRequest($query, int $requestId)
    {
        return $query->where('document_access_request_id', $requestId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('accessed_at', '>=', now()->subDays($days));
    }
}
