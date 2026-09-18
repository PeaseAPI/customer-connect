<?php

namespace App\Enums;

enum LeadFollowUpStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待跟进',
            self::Completed => '已完成',
            self::Cancelled => '已取消',
        };
    }
}