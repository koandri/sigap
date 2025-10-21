<?php

declare(strict_types=1);

namespace App\Enums;

enum AccessType: string
{
    case OneTime = 'one_time';
    case Multiple = 'multiple';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One Time Access',
            self::Multiple => 'Multiple Access',
        };
    }

    public function isOneTime(): bool
    {
        return $this === self::OneTime;
    }

    public function isMultiple(): bool
    {
        return $this === self::Multiple;
    }
}
