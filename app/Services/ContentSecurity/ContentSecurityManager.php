<?php

namespace App\Services\ContentSecurity;

use App\Models\ContentAuditLog;
use InvalidArgumentException;

/**
 * 内容审核管理器 - 根据配置分发到具体Provider
 *
 * 支持Provider: ecloud | aliyun | tencent
 */
class ContentSecurityManager
{
    /** @var array<string, ContentSecurityInterface> 已实例化的Provider缓存 */
    private array $providers = [];

    public function driver(?string $name = null): ContentSecurityInterface
    {
        $name = $name
            ?: \Illuminate\Support\Facades\Cache::get('verification_driver.content_security')
            ?: config('services.content_security.driver', 'aliyun');

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $provider = match ($name) {
            'ecloud'  => new EcloudContentAudit(),
            'aliyun'  => new AliyunContentSecurity(),
            'tencent' => new TencentContentSecurity(),
            default   => throw new InvalidArgumentException("未知的内容审核驱动: {$name}"),
        };

        return $this->providers[$name] = $provider;
    }

    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        return $this->normalize($this->driver()->auditText($content, $auditableType, $auditableId, $categories));
    }

    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        return $this->normalize($this->driver()->auditImage($url, $data, $auditableType, $auditableId, $categories));
    }

    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        return $this->normalize($this->driver()->auditVideo($url, $auditableType, $auditableId, $categories, $callbackUrl));
    }

    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        return $this->normalize($this->driver()->auditAudio($url, $auditableType, $auditableId, $categories, $callbackUrl));
    }

    /**
     * 规范化各Provider的审核结果, 保证调用方兼容:
     * - passed / success 双键(旧代码用passed, 新代码用success)
     * - labels 缺省空数组
     */
    private function normalize(array $result): array
    {
        $ok = $result['success'] ?? $result['passed'] ?? false;

        // 审核服务未配置/未启用时放行业务内容(fail-open):
        // 未启用不等于内容违规, 不应阻断工单/聊天等主业务流程。
        if (($result['suggestion'] ?? '') === 'DISABLED') {
            $ok = true;
        }

        return [
            'passed'     => $ok,
            'success'    => $ok,
            'suggestion' => $result['suggestion'] ?? 'ERROR',
            'labels'     => $result['labels'] ?? $result['record']->labels ?? [],
            'record'     => $result['record'] ?? null,
            'message'    => $result['message'] ?? '',
        ];
    }

    public function handleCallback(array $callbackData): ContentAuditLog
    {
        return $this->driver()->handleCallback($callbackData);
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
