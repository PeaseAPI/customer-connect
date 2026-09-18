<?php

namespace App\Enums;

enum EstimateRequestStatus: string
{
    case Pending = 'pending';
    case Converted = 'converted';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待处理',
            self::Converted => '已转化',
            self::Declined => '已拒绝',
        };
    }
}