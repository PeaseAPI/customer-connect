<?php

namespace App\Enums;

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => '低',
            self::Medium => '中',
            self::High => '高',
            self::Urgent => '紧急',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => '#67C23A',
            self::Medium => '#409EFF',
            self::High => '#E6A23C',
            self::Urgent => '#F56C6C',
        };
    }
}
