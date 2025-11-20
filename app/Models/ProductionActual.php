<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionActual extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_plan_id',
        'production_date',
        'recorded_by',
        'recorded_at',
        'notes',
    ];

    protected $casts = [
        'production_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function step1(): HasMany
    {
        return $this->hasMany(ProductionActualStep1::class);
    }

    public function step2(): HasMany
    {
        return $this->hasMany(ProductionActualStep2::class);
    }

    public function step3(): HasMany
    {
        return $this->hasMany(ProductionActualStep3::class);
    }

    public function step4(): HasMany
    {
        return $this->hasMany(ProductionActualStep4::class);
    }

    public function step5(): HasMany
    {
        return $this->hasMany(ProductionActualStep5::class);
    }

    /**
     * Check if all steps have actual data.
     */
    public function isComplete(): bool
    {
        return $this->step1()->exists()
            && $this->step2()->exists()
            && $this->step3()->exists()
            && $this->step4()->exists()
            && $this->step5()->exists();
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        $steps = [
            $this->step1()->exists(),
            $this->step2()->exists(),
            $this->step3()->exists(),
            $this->step4()->exists(),
            $this->step5()->exists(),
        ];

        $completed = count(array_filter($steps));
        return ($completed / 5) * 100;
    }
}
