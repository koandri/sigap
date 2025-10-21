<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentInstanceStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PendingApproval;
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }
}
