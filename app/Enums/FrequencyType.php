<?php

declare(strict_types=1);

namespace App\Enums;

enum FrequencyType: string
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::HOURLY => 'Hourly',
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::HOURLY => 'Schedule maintenance every X hours',
            self::DAILY => 'Schedule maintenance every X days',
            self::WEEKLY => 'Schedule maintenance on specific day(s) of the week',
            self::MONTHLY => 'Schedule maintenance on specific date(s) each month',
            self::YEARLY => 'Schedule maintenance on a specific date each year',
        };
    }
}

