<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Alipay = 'alipay';
    case Wechat = 'wechat';
    case Stripe = 'stripe';
    case Paypal = 'paypal';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Alipay => 'Alipay',
            self::Wechat => 'WeChat Pay',
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
            self::Offline => 'Offline',
        };
    }
}
