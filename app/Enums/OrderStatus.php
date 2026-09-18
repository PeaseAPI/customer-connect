<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '待处理',
            self::Processing => '处理中',
            self::Completed => '已完成',
            self::Canceled => '已取消',
        };
    }
}