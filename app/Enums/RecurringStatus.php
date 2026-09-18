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
            self::Active => '生效中',
            self::Inactive => '已停用',
            self::Completed => '已完成',
        };
    }
}