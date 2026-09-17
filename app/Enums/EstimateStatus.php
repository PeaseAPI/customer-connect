<?php

namespace App\Enums;

enum EstimateStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待确认',
            self::Accepted => '已接受',
            self::Declined => '已拒绝',
            self::Expired => '已过期',
        };
    }
}
