<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CleaningSchedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'location_id',
        'name',
        'description',
        'frequency_type',
        'frequency_config',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'frequency_config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the location for this cleaning schedule.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get all items in this cleaning schedule.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CleaningScheduleItem::class);
    }

    /**
     * Get all tasks generated from this schedule.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CleaningTask::class);
    }

    /**
     * Get all alerts for this schedule.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(CleaningScheduleAlert::class);
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get human-readable frequency description.
     */
    public function getFrequencyDescriptionAttribute(): string
    {
        $config = $this->frequency_config ?? [];
        
        return match($this->frequency_type) {
            'daily' => $this->getDailyDescription($config),
            'weekly' => $this->getWeeklyDescription($config),
            'monthly' => $this->getMonthlyDescription($config),
            default => 'Unknown frequency',
        };
    }

    private function getDailyDescription(array $config): string
    {
        $interval = $config['interval'] ?? 1;
        return $interval == 1 ? 'Daily' : "Every {$interval} days";
    }

    private function getWeeklyDescription(array $config): string
    {
        $days = $config['days'] ?? [];
        
        $dayNames = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
        ];
        
        if (empty($days)) {
            return 'Weekly';
        }
        
        $daysList = implode(', ', array_map(fn($d) => $dayNames[$d] ?? '', $days));
        return "Every {$daysList}";
    }

    private function getMonthlyDescription(array $config): string
    {
        $dates = $config['dates'] ?? [];
        
        if (empty($dates)) {
            return 'Monthly';
        }
        
        $datesList = implode(', ', array_map(function($d) {
            $suffix = match($d) {
                1, 21, 31 => 'st',
                2, 22 => 'nd',
                3, 23 => 'rd',
                default => 'th',
            };
            return "{$d}{$suffix}";
        }, $dates));
        
        return "Monthly on the {$datesList}";
    }
}
