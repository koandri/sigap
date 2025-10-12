<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'form_field_id',
        'answer_value',
        'answer_metadata'
    ];

    protected $casts = [
        'answer_metadata' => 'array'
    ];

    // Relationships
    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }

    // Helper methods
    public function getDisplayValue()
    {
        // For file type, return file name from metadata
        if ($this->field->field_type === 'file' && $this->answer_metadata) {
            return $this->answer_metadata['filename'] ?? $this->answer_value;
        }

        // For select/checkbox with options
        if ($this->field->hasOptions()) {
            $option = $this->field->options()
                ->where('option_value', $this->answer_value)
                ->first();
            return $option?->option_label ?? $this->answer_value;
        }

        return $this->answer_value;
    }
}