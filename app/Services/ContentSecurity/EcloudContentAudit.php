<?php

namespace App\Services\ContentSecurity;

use App\Models\ContentAuditLog;
use App\Services\Ecloud\EcloudClient;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

/**
 * 移动云内容审核Provider（适配原有ContentAuditService逻辑）
 *
 * 文档: https://ecloud.10086.cn/op-help-center/doc/outline/32923
 * 认证方式: AK/SK + HMAC-SHA256
 * API: /censor/v1/text | /censor/v1/image | /censor/v1/video | /censor/v1/audio
 */
class EcloudContentAudit implements ContentSecurityInterface
{
    private EcloudClient $client;

    public function __construct(?EcloudClient $client = null)
    {
        $this->client = $client ?? new EcloudClient();
    }

    public function isEnabled(): bool
    {
        return config('services.content_security.ecloud.enabled', false)
            && $this->client->isConfigured();
    }

    public function getProviderName(): string
    {
        return 'ecloud';
    }

    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '移动云内容审核服务未启用'];
        }
        if (trim($content) === '') {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容不能为空'];
        }
        if (mb_strlen($content) > 6000) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容超长(最大6000字)'];
        }

        try {
            $response = $this->client->post('/censor/v1/text', [
                'content'    => $content,
                'categories' => $categories,
            ]);

            if (($response['code'] ?? -1) !== '0' && ($response['code'] ?? -1) !== 0) {
                Log::error('Ecloud text audit error', ['message' => $response['message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['message'] ?? '审核服务异常'];
            }

            $data       = $response['data'] ?? [];
            $suggestion = $this->mapSuggestion($data['suggestion'] ?? 'review');
            $hits       = $data['hits'] ?? [];

            $record = ContentAuditLog::create([
                'provider'       => 'ecloud',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'text',
                'content_preview' => mb_substr($content, 0, 2000),
                'content_url'    => null,
                'suggestion'     => $suggestion,
                'labels'         => $hits,
                'status'         => 'COMPLETED',
                'audited_at'     => now(),
                'task_id'        => $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规' : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Ecloud text audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '移动云内容审核服务未启用'];
        }
        if (empty($url) && empty($data)) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '必须提供图片URL或data'];
        }

        try {
            $response = $this->client->post('/censor/v1/image', array_filter([
                'url'        => $url,
                'data'       => $data,
                'categories' => $categories,
            ], fn ($v) => $v !== null));

            if (($response['code'] ?? -1) !== '0' && ($response['code'] ?? -1) !== 0) {
                Log::error('Ecloud image audit error', ['message' => $response['message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['message'] ?? '审核服务异常'];
            }

            $responseData = $response['data'] ?? [];
            $suggestion   = $this->mapSuggestion($responseData['suggestion'] ?? 'review');

            $record = ContentAuditLog::create([
                'provider'       => 'ecloud',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'image',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => $suggestion,
                'labels'         => $responseData['hits'] ?? [],
                'task_id'        => $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规' : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Ecloud image audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '移动云内容审核服务未启用'];
        }

        try {
            $response = $this->client->post('/censor/v1/video', array_filter([
                'url'         => $url,
                'categories'  => $categories,
                'callbackUrl' => $callbackUrl,
            ], fn ($v) => $v !== null));

            if (($response['code'] ?? -1) !== '0' && ($response['code'] ?? -1) !== 0) {
                Log::error('Ecloud video audit error', ['message' => $response['message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['message'] ?? '审核服务异常'];
            }

            $data       = $response['data'] ?? [];
            $isAsync    = ($callbackUrl !== null);
            $suggestion = $isAsync ? 'PENDING' : $this->mapSuggestion($data['suggestion'] ?? 'review');

            $record = ContentAuditLog::create([
                'provider'       => 'ecloud',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'video',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => $suggestion,
                'labels'         => $data['hits'] ?? [],
                'task_id'        => $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => true,
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $isAsync ? '已提交异步审核,结果将通过回调返回' : '审核完成',
            ];
        } catch (\Exception $e) {
            Log::error('Ecloud video audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '移动云内容审核服务未启用'];
        }

        try {
            $response = $this->client->post('/censor/v1/audio', array_filter([
                'url'         => $url,
                'categories'  => $categories,
                'callbackUrl' => $callbackUrl,
            ], fn ($v) => $v !== null));

            if (($response['code'] ?? -1) !== '0' && ($response['code'] ?? -1) !== 0) {
                Log::error('Ecloud audio audit error', ['message' => $response['message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['message'] ?? '审核服务异常'];
            }

            $data       = $response['data'] ?? [];
            $isAsync    = ($callbackUrl !== null);
            $suggestion = $isAsync ? 'PENDING' : $this->mapSuggestion($data['suggestion'] ?? 'review');

            $record = ContentAuditLog::create([
                'provider'       => 'ecloud',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'audio',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => $suggestion,
                'labels'         => $data['hits'] ?? [],
                'task_id'        => $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => true,
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $isAsync ? '已提交异步审核,结果将通过回调返回' : '审核完成',
            ];
        } catch (\Exception $e) {
            Log::error('Ecloud audio audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    /**
     * 处理异步审核回调
     */
    public function handleCallback(array $callbackData): ContentAuditLog
    {
        $requestId = $callbackData['requestId'] ?? '';

        $log = ContentAuditLog::where('provider', 'ecloud')
            ->where('task_id', $requestId)
            ->orderByDesc('created_at')
            ->first();

        if (!$log) {
            throw new \RuntimeException("未找到对应的审核记录: {$requestId}");
        }

        $suggestion = $this->mapSuggestion($callbackData['suggestion'] ?? 'review');

        $log->update([
            'suggestion' => $suggestion,
            'labels'     => $callbackData['hits'] ?? [],
            'status'     => 'COMPLETED',
            'audited_at' => now(),
            'details'    => $callbackData,
        ]);

        return $log;
    }

    /**
     * 统一映射审核建议(pass|block|review)
     */
    private function mapSuggestion(string $raw): string
    {
        return match (strtolower($raw)) {
            'pass', 'ok'      => 'PASS',
            'block', 'reject' => 'BLOCK',
            default           => 'REVIEW',
        };
    }
}
