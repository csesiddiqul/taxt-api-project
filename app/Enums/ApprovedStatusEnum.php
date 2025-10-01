<?php

namespace App\Enums;

enum ApprovedStatusEnum: int
{
    case Approved = 1;
    case NotApproved = 2;
    case Pending = 3;
    case Rejected = 4;
    case InActive = 5;

    public static function asList(): array
    {
        return [
            self::Approved->value => self::Approved,
            self::NotApproved->value => self::NotApproved,
            self::Pending->value => self::Pending,
            self::Rejected->value => self::Rejected,
            self::InActive->value => self::InActive,
        ];
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::NotApproved => 'New Application',
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::InActive => 'Inactive',
        };
    }
}
