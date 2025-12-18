<?php

declare(strict_types=1);

namespace App\Enums;

enum ComponentType: string
{
    case Consumable = 'consumable';
    case Replaceable = 'replaceable';
    case Integral = 'integral';

    public function label(): string
    {
        return match ($this) {
            self::Consumable => 'Consumable',
            self::Replaceable => 'Replaceable',
            self::Integral => 'Integral',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Consumable => 'Items that get used up (e.g., tyres, filters)',
            self::Replaceable => 'Items that can be swapped out (e.g., harddisks, batteries)',
            self::Integral => 'Items that are permanently part of the asset (e.g., GPS tracker)',
        };
    }
}














