<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrintedFormStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PrintedForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_request_item_id',
        'form_number',
        'document_version_id',
        'issued_to',
        'issued_at',
        'status',
        'returned_at',
        'received_at',
        'scanned_at',
        'scanned_file_path',
        'physical_location',
        'notes',
    ];

    protected $casts = [
        'status' => PrintedFormStatus::class,
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
        'received_at' => 'datetime',
        'scanned_at' => 'datetime',
        'physical_location' => 'array',
    ];

    public function formRequestItem(): BelongsTo
    {
        return $this->belongsTo(FormRequestItem::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function scopeByFormNumber($query, string $formNumber)
    {
        return $query->where('form_number', $formNumber);
    }

    public function scopeByStatus($query, PrintedFormStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInCirculation($query)
    {
        return $query->whereIn('status', [
            PrintedFormStatus::Issued,
            PrintedFormStatus::Circulating,
        ]);
    }

    public function scopeReturned($query)
    {
        return $query->whereIn('status', [
            PrintedFormStatus::Returned,
            PrintedFormStatus::Lost,
            PrintedFormStatus::Spoilt,
        ]);
    }

    public function scopeReceived($query)
    {
        return $query->whereIn('status', [
            PrintedFormStatus::Received,
            PrintedFormStatus::Scanned,
        ]);
    }

    public function scopeProblematic($query)
    {
        return $query->whereIn('status', [
            PrintedFormStatus::Lost,
            PrintedFormStatus::Spoilt,
        ]);
    }

    public function scopeByIssuedTo($query, int $userId)
    {
        return $query->where('issued_to', $userId);
    }

    public function scopeByDocumentVersion($query, int $versionId)
    {
        return $query->where('document_version_id', $versionId);
    }

    public function isInCirculation(): bool
    {
        return $this->status->isInCirculation();
    }

    public function isReturned(): bool
    {
        return $this->status->isReturned();
    }

    public function isReceived(): bool
    {
        return $this->status->isReceived();
    }

    public function isProblematic(): bool
    {
        return $this->status->isProblematic();
    }

    public function getFormNameAttribute(): string
    {
        return $this->documentVersion->document->title;
    }

    public function getIssueDateAttribute(): string
    {
        return $this->issued_at->format('Y-m-d');
    }

    public function getPhysicalLocationStringAttribute(): string
    {
        if (!$this->physical_location) {
            return 'Not specified';
        }

        $location = $this->physical_location;
        return sprintf(
            'Room: %s, Cabinet: %s, Shelf: %s',
            $location['room_no'] ?? 'N/A',
            $location['cabinet_no'] ?? 'N/A',
            $location['shelf_no'] ?? 'N/A'
        );
    }
}
