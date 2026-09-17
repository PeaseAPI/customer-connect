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
            self::Alipay => '支付宝',
            self::Wechat => '微信支付',
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
            self::Offline => '线下支付',
        };
    }
}
