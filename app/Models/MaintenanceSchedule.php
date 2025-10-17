<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FrequencyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MaintenanceSchedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_id',
        'maintenance_type_id',
        'frequency_type',
        'frequency_config',
        'frequency_days',
        'last_performed_at',
        'next_due_date',
        'description',
        'checklist',
        'assigned_to',
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
        'last_performed_at' => 'datetime',
        'next_due_date' => 'datetime',
        'checklist' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the asset that owns the schedule.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the maintenance type for this schedule.
     */
    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    /**
     * Get the user assigned to this schedule.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get overdue schedules.
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now());
    }

    /**
     * Scope to get upcoming schedules.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('next_due_date', [now(), now()->addDays($days)]);
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
        return $interval == 1 ? 'Every hour' : "Every {$interval} hours";
    }

    private function getDailyDescription(array $config): string
    {
        $interval = $config['interval'] ?? $this->frequency_days ?? 1;
        return $interval == 1 ? 'Daily' : "Every {$interval} days";
    }

    private function getWeeklyDescription(array $config): string
    {
        $interval = $config['interval'] ?? 1;
        $days = $config['days'] ?? [];
        
        $dayNames = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
        ];
        
        if (empty($days)) {
            return $interval == 1 ? 'Weekly' : "Every {$interval} weeks";
        }
        
        $daysList = implode(', ', array_map(fn($d) => $dayNames[$d] ?? '', $days));
        $prefix = $interval == 1 ? 'Every' : "Every {$interval} weeks on";
        
        return "{$prefix} {$daysList}";
    }

    private function getMonthlyDescription(array $config): string
    {
        $interval = $config['interval'] ?? 1;
        $type = $config['type'] ?? 'date'; // date, last_day, weekday
        
        $prefix = $interval == 1 ? 'Monthly' : "Every {$interval} months";
        
        if ($type === 'last_day') {
            return "{$prefix} on the last day";
        }
        
        if ($type === 'weekday') {
            $week = $config['week'] ?? 1; // 1=first, 2=second, 3=third, 4=fourth, 5=last
            $day = $config['day'] ?? 1;
            
            $weekNames = [1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'last'];
            $dayNames = [
                1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 
                4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
            ];
            
            return "{$prefix} on the {$weekNames[$week]} {$dayNames[$day]}";
        }
        
        // Default: specific date
        $date = $config['date'] ?? 1;
        $suffix = match($date) {
            1, 21, 31 => 'st',
            2, 22 => 'nd',
            3, 23 => 'rd',
            default => 'th',
        };
        
        return "{$prefix} on the {$date}{$suffix}";
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
        
        return "Yearly on {$monthNames[$month]} {$date}{$suffix}";
    }
}