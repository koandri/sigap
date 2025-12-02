<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentBorrowStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case CheckedOut = 'checked_out';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::CheckedOut => 'Checked Out',
            self::Returned => 'Returned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::CheckedOut => 'primary',
            self::Returned => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'fa-clock',
            self::Approved => 'fa-check',
            self::Rejected => 'fa-times',
            self::CheckedOut => 'fa-book-reader',
            self::Returned => 'fa-check-circle',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Approved, self::CheckedOut]);
    }

    public function canBeApproved(): bool
    {
        return $this === self::Pending;
    }

    public function canBeCheckedOut(): bool
    {
        return $this === self::Approved;
    }

    public function canBeReturned(): bool
    {
        return $this === self::CheckedOut;
    }
}

