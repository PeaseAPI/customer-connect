<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class PaymentManager
{
    public function driver(string $gateway): PaymentServiceInterface
    {
        return match ($gateway) {
            'alipay' => new AlipayService(),
            'wechat' => new WechatPayService(),
            default => throw new \InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }

    public function createOrder(string $gateway, array $orderData): array
    {
        try {
            return $this->driver($gateway)->createOrder($orderData);
        } catch (\Exception $e) {
            Log::error('Payment create order failed', ['gateway' => $gateway, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyCallback(string $gateway, array $callbackData): bool
    {
        try {
            return $this->driver($gateway)->verifyCallback($callbackData);
        } catch (\Exception $e) {
            Log::error('Payment callback signature verification failed', ['gateway' => $gateway, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
