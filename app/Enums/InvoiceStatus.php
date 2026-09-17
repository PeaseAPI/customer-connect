<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Paid = 'paid';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Sent => '已发送',
            self::Partial => '部分支付',
            self::Paid => '已支付',
            self::Canceled => '已取消',
        };
    }
}
