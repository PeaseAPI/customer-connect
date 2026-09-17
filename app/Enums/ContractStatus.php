<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Active => '生效中',
            self::Expired => '已过期',
            self::Canceled => '已取消',
        };
    }
}
