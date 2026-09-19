<?php

namespace App\Services\IdentityVerification;

use InvalidArgumentException;

/**
 * 实人认证管理器 - 根据配置分发到具体Provider
 *
 * 支持Provider: aliyun | tencent | alipay | wechat
 */
class IdentityVerifyManager
{
    /** @var array<string, IdentityVerifyInterface> 已实例化的Provider缓存 */
    private array $providers = [];

    public function driver(?string $name = null): IdentityVerifyInterface
    {
        $name = $name
            ?: \Illuminate\Support\Facades\Cache::get('verification_driver.identity_verify')
            ?: config('services.identity_verify.driver', 'aliyun');

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $provider = match ($name) {
            'aliyun'  => new AliyunIdentityVerify(),
            'tencent' => new TencentIdentityVerify(),
            'alipay'  => new AlipayIdentityVerify(),
            'wechat'  => new WechatIdentityVerify(),
            default   => throw new InvalidArgumentException("未知的实人认证驱动: {$name}"),
        };

        return $this->providers[$name] = $provider;
    }

    public function idCardVerify(string $name, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->driver()->idCardVerify($name, $idNumber, $verifiableType, $verifiableId);
    }

    public function phoneThreeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->driver()->phoneThreeFactorVerify($name, $phone, $idNumber, $verifiableType, $verifiableId);
    }

    public function bankCardVerify(string $name, string $bankCard, ?string $idNumber = null, ?string $phone = null, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->driver()->bankCardVerify($name, $bankCard, $idNumber, $phone, $verifiableType, $verifiableId);
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
        foreach (['aliyun', 'tencent', 'alipay', 'wechat'] as $name) {
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
