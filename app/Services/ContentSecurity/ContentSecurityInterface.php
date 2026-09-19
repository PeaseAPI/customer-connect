<?php

namespace App\Services\ContentSecurity;

use App\Models\ContentAuditLog;

/**
 * 内容审核/安全统一接口
 * 
 * 实现方: EcloudContentAudit, AliyunContentSecurity, TencentContentSecurity
 */
interface ContentSecurityInterface
{
    /**
     * 文本审核（同步）
     */
    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array;

    /**
     * 图片审核（同步）
     */
    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array;

    /**
     * 视频审核（异步+回调）
     */
    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array;

    /**
     * 音频审核（异步+回调）
     */
    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array;

    /**
     * 异步审核回调处理
     */
    public function handleCallback(array $callbackData): ContentAuditLog;

    /**
     * 服务是否启用
     */
    public function isEnabled(): bool;

    /**
     * 供应商标识
     */
    public function getProviderName(): string;
}
