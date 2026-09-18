<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待支付',
            self::Completed => '已支付',
            self::Failed => '支付失败',
            self::Refunded => '已退款',
        };
    }
}