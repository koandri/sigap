<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_date',
        'production_start_date',
        'ready_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'production_start_date' => 'date',
        'ready_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function step1(): HasMany
    {
        return $this->hasMany(ProductionPlanStep1::class);
    }

    public function step2(): HasMany
    {
        return $this->hasMany(ProductionPlanStep2::class);
    }

    public function step3(): HasMany
    {
        return $this->hasMany(ProductionPlanStep3::class);
    }

    public function step4(): HasMany
    {
        return $this->hasMany(ProductionPlanStep4::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }
}
