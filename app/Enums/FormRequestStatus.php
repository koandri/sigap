<?php

declare(strict_types=1);

namespace App\Enums;

enum FormRequestStatus: string
{
    case Requested = 'requested';
    case Acknowledged = 'acknowledged';
    case Processing = 'processing';
    case Ready = 'ready_for_collection';
    case Collected = 'collected';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Acknowledged => 'Acknowledged',
            self::Processing => 'Processing',
            self::Ready => 'Ready for Collection',
            self::Collected => 'Collected',
            self::Completed => 'Completed',
        };
    }

    public function isRequested(): bool
    {
        return $this === self::Requested;
    }

    public function isAcknowledged(): bool
    {
        return $this === self::Acknowledged;
    }

    public function isProcessing(): bool
    {
        return $this === self::Processing;
    }

    public function isReady(): bool
    {
        return $this === self::Ready;
    }

    public function isCollected(): bool
    {
        return $this === self::Collected;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
