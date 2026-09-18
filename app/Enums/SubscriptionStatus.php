<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trial = 'trial';
    case Expired = 'expired';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Active => '生效中',
            self::Trial => '试用中',
            self::Expired => '已过期',
            self::Canceled => '已取消',
        };
    }
}