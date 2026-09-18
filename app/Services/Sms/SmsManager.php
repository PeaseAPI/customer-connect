<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class SmsManager
{
    private ?SmsServiceInterface $driver = null;

    /**
     * 获取当前短信驱动
     */
    public function driver(?string $driver = null): SmsServiceInterface
    {
        $driver = $driver ?? config('services.sms.driver', 'aliyun');

        return match ($driver) {
            'aliyun' => new AliyunSmsService(),
            'tencent' => new TencentSmsService(),
            default => throw new \InvalidArgumentException("Unsupported SMS driver: {$driver}"),
        };
    }

    /**
     * 发送验证码
     */
    public function sendVerificationCode(string $mobile, string $code, ?string $driver = null): bool
    {
        try {
            return $this->driver($driver)->sendVerificationCode($mobile, $code);
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['mobile' => $mobile, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 发送通知短信
     */
    public function sendNotification(string $mobile, string $templateId, array $params, ?string $driver = null): bool
    {
        try {
            return $this->driver($driver)->sendNotification($mobile, $templateId, $params);
        } catch (\Exception $e) {
            Log::error('Notification SMS send failed', ['mobile' => $mobile, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
