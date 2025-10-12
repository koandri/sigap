<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BomTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'bom_type_id',
        'code',
        'name',
        'description',
        'output_item_id',
        'output_quantity',
        'output_unit',
        'version',
        'created_by',
        'is_active',
        'is_template',
        'parent_template_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'output_quantity' => 'decimal:3',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
    ];

    /**
     * Get the BoM type that owns this template.
     */
    public function bomType(): BelongsTo
    {
        return $this->belongsTo(BomType::class);
    }

    /**
     * Get the output item that this BoM produces.
     */
    public function outputItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'output_item_id');
    }

    /**
     * Get the user who created this template.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent template if this was copied from another.
     */
    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(BomTemplate::class, 'parent_template_id');
    }

    /**
     * Get child templates copied from this one.
     */
    public function childTemplates(): HasMany
    {
        return $this->hasMany(BomTemplate::class, 'parent_template_id');
    }

    /**
     * Get all ingredients for this template.
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(BomIngredient::class)->orderBy('sort_order');
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates that can be used as base templates.
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Get the full template name with code.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
