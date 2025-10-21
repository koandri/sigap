<?php

declare(strict_types=1);

namespace App\Enums;

enum ApprovalTier: string
{
    case Manager = 'manager';
    case ManagementRepresentative = 'management_representative';

    public function label(): string
    {
        return match ($this) {
            self::Manager => 'Manager',
            self::ManagementRepresentative => 'Management Representative',
        };
    }

    public function isManager(): bool
    {
        return $this === self::Manager;
    }

    public function isManagementRepresentative(): bool
    {
        return $this === self::ManagementRepresentative;
    }
}
