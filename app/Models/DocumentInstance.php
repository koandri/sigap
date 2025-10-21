<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentInstanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DocumentInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_document_version_id',
        'instance_number',
        'subject',
        'content_summary',
        'created_by',
        'status',
        'final_pdf_path',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'status' => DocumentInstanceStatus::class,
        'approved_at' => 'datetime',
    ];

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'template_document_version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeByTemplate($query, int $templateVersionId)
    {
        return $query->where('template_document_version_id', $templateVersionId);
    }

    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', DocumentInstanceStatus::PendingApproval);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', DocumentInstanceStatus::Approved);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', DocumentInstanceStatus::Draft);
    }

    public function isDraft(): bool
    {
        return $this->status === DocumentInstanceStatus::Draft;
    }

    public function isPending(): bool
    {
        return $this->status === DocumentInstanceStatus::PendingApproval;
    }

    public function isApproved(): bool
    {
        return $this->status === DocumentInstanceStatus::Approved;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft() && $this->created_by === auth()->id();
    }
}
