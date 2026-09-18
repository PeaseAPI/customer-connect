<?php

namespace App\Services\Payment;

interface PaymentServiceInterface
{
    /**
     * Create payment order
     */
    public function createOrder(array $orderData): array;

    /**
     * 验证回调签名
     */
    public function verifyCallback(array $callbackData): bool;

    /**
     * 退款
     */
    public function refund(string $transactionId, float $amount, string $reason = ''): array;

    /**
     * 查询订单Status
     */
    public function queryOrder(string $outTradeNo): array;

    /**
     * 测试连接
     */
    public function testConnection(): bool;

    /**
     * 获取配置
     */
    public function getConfig(): array;

    /**
     * 设置配置
     */
    public function setConfig(array $config): void;
}
