<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ContentSecurity\ContentSecurityManager;
use App\Services\IdentityVerification\IdentityVerifyManager;
use App\Services\PhoneVerification\PhoneVerifyManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * 验证服务Provider管理控制器
 *
 * 提供三类服务(实人认证/号码认证/内容审核)的:
 * - 全部Provider配置状态(供后台配置页展示)
 * - 联通性测试端点(验证凭证是否有效)
 */
class VerificationProviderController extends BaseApiController
{
    /**
     * 获取三类服务所有Provider的配置状态
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'identity_verify' => [
                'driver' => config('services.identity_verify.driver'),
                'providers' => app(IdentityVerifyManager::class)->getProvidersStatus(),
            ],
            'phone_verify' => [
                'driver' => config('services.phone_verify.driver'),
                'providers' => app(PhoneVerifyManager::class)->getProvidersStatus(),
            ],
            'content_security' => [
                'driver' => config('services.content_security.driver'),
                'providers' => app(ContentSecurityManager::class)->getProvidersStatus(),
            ],
        ]);
    }

    /**
     * 测试指定服务的连通性
     *
     * service: identity_verify | phone_verify | content_security
     * 说明: 通过 isEnabled() 检查凭证是否完整, 不实际调用付费API
     */
    public function test(Request $request, string $service): JsonResponse
    {
        if (!in_array($service, ['identity_verify', 'phone_verify', 'content_security'])) {
            return $this->error('无效的服务类型');
        }

        $result = match ($service) {
            'identity_verify'  => $this->testIdentityVerify(),
            'phone_verify'     => $this->testPhoneVerify(),
            'content_security' => $this->testContentSecurity(),
        };

        return $this->success($result, $result['enabled'] ? '服务已启用' : '服务未启用');
    }

    /**
     * 切换指定服务的当前Provider驱动(运行时覆盖, 优先于.env配置)
     *
     * 覆盖值存储于缓存(永久), 如需回退到env配置, 将driver设为空字符串
     */
    public function setDriver(Request $request, string $service): JsonResponse
    {
        $validDrivers = [
            'identity_verify'  => ['aliyun', 'tencent', 'alipay', 'wechat'],
            'phone_verify'     => ['ecloud', 'aliyun', 'tencent'],
            'content_security' => ['ecloud', 'aliyun', 'tencent'],
        ];

        if (!isset($validDrivers[$service])) {
            return $this->error('无效的服务类型');
        }

        $request->validate([
            'driver' => 'nullable|string|in:' . implode(',', array_merge($validDrivers[$service], [''])),
        ]);

        $driver = (string) $request->input('driver', '');
        $key    = "verification_driver.{$service}";

        if ($driver === '') {
            Cache::forget($key);
            $active = config("services.{$service}.driver");
            $message = '已回退到.env配置驱动';
        } else {
            Cache::forever($key, $driver);
            $active = $driver;
            $message = '驱动已切换';
        }

        // 立即实例化验证新驱动可用性
        $manager = match ($service) {
            'identity_verify'  => app(IdentityVerifyManager::class),
            'phone_verify'     => app(PhoneVerifyManager::class),
            'content_security' => app(ContentSecurityManager::class),
        };
        $provider = $manager->driver($driver ?: null);

        return $this->success([
            'service'         => $service,
            'active_driver'   => $active,
            'provider_name'   => $provider->getProviderName(),
            'provider_configured' => $provider->isEnabled(),
        ], $message);
    }

    private function testIdentityVerify(): array
    {
        $manager = app(IdentityVerifyManager::class);
        return [
            'service' => 'identity_verify',
            'current_provider' => $manager->getProviderName(),
            'enabled' => $manager->isEnabled(),
            'providers' => $manager->getProvidersStatus(),
        ];
    }

    private function testPhoneVerify(): array
    {
        $manager = app(PhoneVerifyManager::class);
        return [
            'service' => 'phone_verify',
            'current_provider' => $manager->getProviderName(),
            'enabled' => $manager->isEnabled(),
            'providers' => $manager->getProvidersStatus(),
        ];
    }

    private function testContentSecurity(): array
    {
        $manager = app(ContentSecurityManager::class);
        return [
            'service' => 'content_security',
            'current_provider' => $manager->getProviderName(),
            'enabled' => $manager->isEnabled(),
            'providers' => $manager->getProvidersStatus(),
        ];
    }
}
