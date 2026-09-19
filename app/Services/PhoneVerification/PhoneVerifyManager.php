<?php

namespace App\Services\PhoneVerification;

use InvalidArgumentException;

/**
 * 号码认证管理器 - 根据配置分发到具体Provider
 *
 * 支持Provider: ecloud | aliyun | tencent
 */
class PhoneVerifyManager
{
    /** @var array<string, PhoneVerifyInterface> 已实例化的Provider缓存 */
    private array $providers = [];

    public function driver(?string $name = null): PhoneVerifyInterface
    {
        $name = $name
            ?: \Illuminate\Support\Facades\Cache::get('verification_driver.phone_verify')
            ?: config('services.phone_verify.driver', 'ecloud');

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $provider = match ($name) {
            'ecloud'  => new EcloudPhoneVerify(),
            'aliyun'  => new AliyunPhoneVerify(),
            'tencent' => new TencentPhoneVerify(),
            default   => throw new InvalidArgumentException("未知的号码认证驱动: {$name}"),
        };

        return $this->providers[$name] = $provider;
    }

    public function twoFactorVerify(string $name, string $phone, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->driver()->twoFactorVerify($name, $phone, $verifiableType, $verifiableId);
    }

    public function threeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->driver()->threeFactorVerify($name, $phone, $idNumber, $verifiableType, $verifiableId);
    }

    public function isEnabled(): bool
    {
        return $this->driver()->isEnabled();
    }

    public function getProviderName(): string
    {
        return $this->driver()->getProviderName();
    }

    /**
     * 获取所有已配置的Provider状态（用于后台配置页）
     */
    public function getProvidersStatus(): array
    {
        $status = [];
        foreach (['ecloud', 'aliyun', 'tencent'] as $name) {
            try {
                $provider = $this->driver($name);
                $status[$name] = [
                    'configured' => $provider->isEnabled(),
                    'is_current' => $name === $this->getProviderName(),
                ];
            } catch (\Throwable $e) {
                $status[$name] = ['configured' => false, 'is_current' => false, 'error' => $e->getMessage()];
            }
        }
        return $status;
    }
}
