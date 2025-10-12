<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BomType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'stage',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the BoM templates for this type.
     */
    public function bomTemplates(): HasMany
    {
        return $this->hasMany(BomTemplate::class);
    }

    /**
     * Get active BoM templates for this type.
     */
    public function activeBomTemplates(): HasMany
    {
        return $this->hasMany(BomTemplate::class)->where('is_active', true);
    }

    /**
     * Scope to get only active BoM types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get BoM types by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get BoM types by stage.
     */
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Get job costing BoM types.
     */
    public function scopeJobCosting($query)
    {
        return $query->where('category', 'job_costing');
    }

    /**
     * Get roll over BoM types.
     */
    public function scopeRollOver($query)
    {
        return $query->where('category', 'roll_over');
    }

    /**
     * Get the full name with category and stage.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Check if this is a job costing type.
     */
    public function isJobCosting(): bool
    {
        return $this->category === 'job_costing';
    }

    /**
     * Check if this is a roll over type.
     */
    public function isRollOver(): bool
    {
        return $this->category === 'roll_over';
    }
}
