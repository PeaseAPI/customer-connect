<?php

namespace App\Services\Sms;

interface SmsServiceInterface
{
    /**
     * 发送验证码短信
     */
    public function sendVerificationCode(string $mobile, string $code): bool;

    /**
     * 发送通知短信
     */
    public function sendNotification(string $mobile, string $templateId, array $params): bool;

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
