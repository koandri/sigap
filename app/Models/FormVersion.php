<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'version_number',
        'description',
        'is_active',
        'metadata',
        'created_by',
        'created_on'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_on' => 'datetime'
    ];

    // Relationships
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function activate()
    {
        // Deactivate other versions
        $this->form->versions()->update(['is_active' => false]);
        
        // Activate this version
        $this->update(['is_active' => true]);
    }

    public function getFieldsInOrder()
    {
        return $this->fields()->ordered()->get();
    }

    // Add relationship with ordering
    public function orderedFields()
    {
        return $this->hasMany(FormField::class)->ordered();
    }
}