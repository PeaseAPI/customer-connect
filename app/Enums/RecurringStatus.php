<?php

namespace App\Enums;

enum RecurringStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Disabled',
            self::Completed => 'Completed',
        };
    }
}