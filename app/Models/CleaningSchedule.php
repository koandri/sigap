<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FrequencyType;
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
        'scheduled_time',
        'start_time',
        'end_time',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'frequency_type' => FrequencyType::class,
        'frequency_config' => 'array',
        'scheduled_time' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
            FrequencyType::HOURLY => $this->getHourlyDescription($config),
            FrequencyType::DAILY => $this->getDailyDescription($config),
            FrequencyType::WEEKLY => $this->getWeeklyDescription($config),
            FrequencyType::MONTHLY => $this->getMonthlyDescription($config),
            FrequencyType::YEARLY => $this->getYearlyDescription($config),
            default => 'Unknown frequency',
        };
    }

    private function getHourlyDescription(array $config): string
    {
        $interval = $config['interval'] ?? 1;
        $base = $interval == 1 ? 'Every hour' : "Every {$interval} hours";
        
        if ($this->start_time && $this->end_time) {
            $start = $this->start_time->format('g:ia');
            $end = $this->end_time->format('g:ia');
            return "{$base} ({$start} - {$end})";
        }
        
        return $base;
    }

    private function getDailyDescription(array $config): string
    {
        $interval = $config['interval'] ?? 1;
        $base = $interval == 1 ? 'Daily' : "Every {$interval} days";
        
        if ($this->scheduled_time) {
            $time = $this->scheduled_time->format('g:ia');
            return "{$base} at {$time}";
        }
        
        return $base;
    }

    private function getWeeklyDescription(array $config): string
    {
        $days = $config['days'] ?? [];
        
        $dayNames = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'
        ];
        
        if (empty($days)) {
            $base = 'Weekly';
        } else {
            $daysList = implode(', ', array_map(fn($d) => $dayNames[$d] ?? '', $days));
            $base = "Every {$daysList}";
        }
        
        if ($this->scheduled_time) {
            $time = $this->scheduled_time->format('g:ia');
            return "{$base} at {$time}";
        }
        
        return $base;
    }

    private function getMonthlyDescription(array $config): string
    {
        $dates = $config['dates'] ?? [];
        
        if (empty($dates)) {
            $base = 'Monthly';
        } else {
            $datesList = implode(', ', array_map(function($d) {
                $suffix = match($d) {
                    1, 21, 31 => 'st',
                    2, 22 => 'nd',
                    3, 23 => 'rd',
                    default => 'th',
                };
                return "{$d}{$suffix}";
            }, $dates));
            $base = "Monthly on the {$datesList}";
        }
        
        if ($this->scheduled_time) {
            $time = $this->scheduled_time->format('g:ia');
            return "{$base} at {$time}";
        }
        
        return $base;
    }

    private function getYearlyDescription(array $config): string
    {
        $month = $config['month'] ?? 1;
        $date = $config['date'] ?? 1;
        
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        $suffix = match($date) {
            1, 21, 31 => 'st',
            2, 22 => 'nd',
            3, 23 => 'rd',
            default => 'th',
        };
        
        $base = "Yearly on {$monthNames[$month]} {$date}{$suffix}";
        
        if ($this->scheduled_time) {
            $time = $this->scheduled_time->format('g:ia');
            return "{$base} at {$time}";
        }
        
        return $base;
    }
}
