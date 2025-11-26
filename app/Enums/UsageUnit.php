<?php

declare(strict_types=1);

namespace App\Enums;

enum UsageUnit: string
{
    case Days = 'days';
    case Kilometers = 'kilometers';
    case MachineHours = 'machine_hours';
    case Cycles = 'cycles';

    public function label(): string
    {
        return match ($this) {
            self::Days => 'Days',
            self::Kilometers => 'Kilometers',
            self::MachineHours => 'Machine Hours',
            self::Cycles => 'Cycles',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Days => 'Lifetime measured in days',
            self::Kilometers => 'Lifetime measured in kilometers',
            self::MachineHours => 'Lifetime measured in machine hours',
            self::Cycles => 'Lifetime measured in cycles',
        };
    }

    public function isUsageBased(): bool
    {
        return match ($this) {
            self::Days => false,
            self::Kilometers, self::MachineHours, self::Cycles => true,
        };
    }
}





