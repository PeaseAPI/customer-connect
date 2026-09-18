<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Review => 'Review',
            self::Completed => 'Completed',
            self::Cancelled => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => '#909399',
            self::InProgress => '#409EFF',
            self::Review => '#E6A23C',
            self::Completed => '#67C23A',
            self::Cancelled => '#F56C6C',
        };
    }
}
