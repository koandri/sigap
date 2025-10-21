<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentVersionStatus: string
{
    case Draft = 'draft';
    case PendingManagerApproval = 'pending_manager_approval';
    case PendingMgmtApproval = 'pending_mgmt_approval';
    case Active = 'active';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingManagerApproval => 'Pending Manager Approval',
            self::PendingMgmtApproval => 'Pending Management Approval',
            self::Active => 'Active',
            self::Superseded => 'Superseded',
        };
    }

    public function isPending(): bool
    {
        return match ($this) {
            self::PendingManagerApproval, self::PendingMgmtApproval => true,
            default => false,
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Active, self::Superseded => true,
            default => false,
        };
    }
}
