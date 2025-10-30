<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'file_type',
        'is_ncr_paper',
        'status',
        'created_by',
        'revision_description',
        'finalized_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_ncr_paper' => 'boolean',
        'status' => DocumentVersionStatus::class,
        'finalized_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentVersionApproval::class);
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(DocumentAccessRequest::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function formRequestItems(): HasMany
    {
        return $this->hasMany(FormRequestItem::class);
    }

    public function printedForms(): HasMany
    {
        return $this->hasMany(PrintedForm::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(DocumentInstance::class, 'template_document_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', DocumentVersionStatus::Active);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            DocumentVersionStatus::PendingManagerApproval,
            DocumentVersionStatus::PendingMgmtApproval,
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', DocumentVersionStatus::Draft);
    }

    public function scopeSuperseded($query)
    {
        return $query->where('status', DocumentVersionStatus::Superseded);
    }

    public function scopeByDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    public function isActive(): bool
    {
        return $this->status === DocumentVersionStatus::Active;
    }

    public function isDraft(): bool
    {
        return $this->status === DocumentVersionStatus::Draft;
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft() && $this->created_by === auth()->id();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft() && $this->created_by === auth()->id();
    }
}
