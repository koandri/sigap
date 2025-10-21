<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FormRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_request_id',
        'document_version_id',
        'quantity',
    ];

    public function formRequest(): BelongsTo
    {
        return $this->belongsTo(FormRequest::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function printedForms(): HasMany
    {
        return $this->hasMany(PrintedForm::class);
    }

    public function scopeByFormRequest($query, int $requestId)
    {
        return $query->where('form_request_id', $requestId);
    }

    public function scopeByDocumentVersion($query, int $versionId)
    {
        return $query->where('document_version_id', $versionId);
    }
}
