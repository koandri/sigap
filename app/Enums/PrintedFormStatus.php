<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintedFormStatus: string
{
    case Issued = 'issued';
    case Circulating = 'circulating';
    case Returned = 'returned';
    case Lost = 'lost';
    case Spoilt = 'spoilt';
    case Received = 'received';
    case Scanned = 'scanned';

    public function label(): string
    {
        return match ($this) {
            self::Issued => 'Issued',
            self::Circulating => 'Circulating',
            self::Returned => 'Returned',
            self::Lost => 'Lost',
            self::Spoilt => 'Spoilt',
            self::Received => 'Received',
            self::Scanned => 'Scanned',
        };
    }

    public function isInCirculation(): bool
    {
        return match ($this) {
            self::Issued, self::Circulating => true,
            default => false,
        };
    }

    public function isReturned(): bool
    {
        return match ($this) {
            self::Returned, self::Lost, self::Spoilt => true,
            default => false,
        };
    }

    public function isReceived(): bool
    {
        return match ($this) {
            self::Received, self::Scanned => true,
            default => false,
        };
    }

    public function isProblematic(): bool
    {
        return match ($this) {
            self::Lost, self::Spoilt => true,
            default => false,
        };
    }
}
