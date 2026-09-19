<?php

namespace App\Services\ContentSecurity;

use App\Models\ContentAuditLog;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 腾讯云内容安全Provider
 *
 * API文档: https://cloud.tencent.com/document/product/1124
 * 认证方式: TC3-HMAC-SHA256 (SecretId+SecretKey)
 * Endpoint: cms.tencentcloudapi.com | API版本: 2019-03-21
 *
 * 核心API:
 * - TextModeration  文本审核(同步)
 * - ImageModeration 图片审核(同步)
 * - CreateAudioModerationTask   音频审核(异步,回调)
 * - CreateVideoModerationTask   视频审核(异步,回调)
 */
class TencentContentSecurity implements ContentSecurityInterface
{
    private string $secretId;
    private string $secretKey;
    private string $region;
    private bool $enabled;

    public function __construct()
    {
        $this->secretId  = config('services.content_security.tencent.secret_id', '');
        $this->secretKey = config('services.content_security.tencent.secret_key', '');
        $this->region    = config('services.content_security.tencent.region', 'ap-guangzhou');
        $this->enabled   = config('services.content_security.tencent.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->secretId) && !empty($this->secretKey);
    }

    public function getProviderName(): string
    {
        return 'tencent';
    }

    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '腾讯云内容安全服务未启用'];
        }
        if (trim($content) === '') {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容不能为空'];
        }
        if (mb_strlen($content) > 10000) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容超长(最大10000字)'];
        }

        try {
            $response = $this->tc3Request('TextModeration', [
                'Content' => base64_encode($content),
                'BizType' => 'kht',
            ]);

            if (isset($response['Response']['Error'])) {
                $err = $response['Response']['Error'];
                Log::error('Tencent text audit error', ['code' => $err['Code'] ?? '', 'message' => $err['Message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $err['Message'] ?? '审核服务异常'];
            }

            $data       = $response['Response'] ?? [];
            // 腾讯云响应: {"Response":{"Suggestion":"Pass|Block|Review","Label":"...","RequestId":"..."}}
            $suggestion = $this->mapSuggestion($data['Suggestion'] ?? 'Review');
            $label      = $data['Label'] ?? '';
            $detail     = $data['DetailResults'] ?? [];

            $record = ContentAuditLog::create([
                'provider'       => 'tencent',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'text',
                'content_preview' => mb_substr($content, 0, 2000),
                'content_url'    => null,
                'suggestion'     => $suggestion,
                'labels'         => array_map(fn ($d) => ['label' => $d['Label'] ?? '', 'score' => $d['Score'] ?? 0], $detail),
                'status'         => 'COMPLETED',
                'audited_at'     => now(),
                'task_id'        => $data['RequestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规: ' . $label : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Tencent text audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '腾讯云内容安全服务未启用'];
        }
        if (empty($url) && empty($data)) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '必须提供图片URL或data'];
        }

        try {
            $payload = array_filter([
                'BizType'  => 'kht',
                'FileUrl'  => $url,
                'FileContent' => $data ? base64_encode($data) : null,
            ], fn ($v) => $v !== null);

            $response = $this->tc3Request('ImageModeration', $payload);

            if (isset($response['Response']['Error'])) {
                $err = $response['Response']['Error'];
                Log::error('Tencent image audit error', ['code' => $err['Code'] ?? '', 'message' => $err['Message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $err['Message'] ?? '审核服务异常'];
            }

            $data2      = $response['Response'] ?? [];
            $suggestion = $this->mapSuggestion($data2['Suggestion'] ?? 'Review');
            $label      = $data2['Label'] ?? '';

            $record = ContentAuditLog::create([
                'provider'       => 'tencent',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'image',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => $suggestion,
                'labels'         => [['label' => $label, 'score' => $data2['Score'] ?? 0]],
                'task_id'        => $data2['RequestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规: ' . $label : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Tencent image audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        return $this->asyncAudit('CreateVideoModerationTask', 'video', $url, $auditableType, $auditableId, $callbackUrl);
    }

    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        return $this->asyncAudit('CreateAudioModerationTask', 'audio', $url, $auditableType, $auditableId, $callbackUrl);
    }

    /**
     * 异步审核(视频/音频)
     */
    private function asyncAudit(string $action, string $type, string $url, ?string $auditableType, ?int $auditableId, ?string $callbackUrl): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '腾讯云内容安全服务未启用'];
        }

        try {
            $task = array_filter([
                'BizType'  => 'kht',
                'FileUrl'  => $url,
                'CallbackUrl' => $callbackUrl,
            ], fn ($v) => $v !== null);

            $payloadKey = $action === 'CreateVideoModerationTask' ? 'Tasks' : 'Tasks';
            $response   = $this->tc3Request($action, [$payloadKey => [$task]]);

            if (isset($response['Response']['Error'])) {
                $err = $response['Response']['Error'];
                Log::error("Tencent {$type} audit error", ['code' => $err['Code'] ?? '', 'message' => $err['Message'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $err['Message'] ?? '审核服务异常'];
            }

            $data   = $response['Response'] ?? [];
            $taskId = $data['Results'][0]['TaskId'] ?? $data['RequestId'] ?? '';

            $record = ContentAuditLog::create([
                'provider'       => 'tencent',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => $type,
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => 'PENDING',
                'labels'         => [],
                'task_id'        => (string) $taskId,
                'details'        => $response,
            ]);

            return [
                'success'    => true,
                'suggestion' => 'PENDING',
                'record'     => $record,
                'message'    => '已提交异步审核,结果将通过回调返回',
            ];
        } catch (\Exception $e) {
            Log::error("Tencent {$type} audit exception", ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    /**
     * 处理异步审核回调
     */
    public function handleCallback(array $callbackData): ContentAuditLog
    {
        $taskIds = collect($callbackData['Results'] ?? [])
            ->pluck('TaskId')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $log = ContentAuditLog::where('provider', 'tencent')
            ->whereIn('task_id', $taskIds)
            ->orderByDesc('created_at')
            ->first();

        if (!$log) {
            throw new \RuntimeException('未找到对应的审核记录: ' . json_encode($taskIds));
        }

        $results    = $callbackData['Results'][0]['SegmentResults'] ?? [];
        $suggestion = $this->mapSuggestion($callbackData['Results'][0]['Suggestion'] ?? 'Review');

        $log->update([
            'suggestion' => $suggestion,
            'labels'     => array_map(fn ($r) => ['label' => $r['Label'] ?? '', 'score' => $r['Score'] ?? 0], $results),
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
            'pass'  => 'PASS',
            'block' => 'BLOCK',
            default => 'REVIEW',
        };
    }

    /**
     * 腾讯云TC3签名请求
     */
    private function tc3Request(string $action, array $payload): array
    {
        $service   = 'cms';
        $version   = '2019-03-21';
        $timestamp = time();
        $date      = gmdate('Y-m-d', $timestamp);
        $body      = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $endpoint  = 'cms.tencentcloudapi.com';

        $canonicalRequest = implode("\n", [
            'POST', '/', '',
            "content-type:application/json\nhost:{$endpoint}\nx-tc-action:" . strtolower($action),
            'content-type;host;x-tc-action',
            hash('sha256', $body),
        ]);

        $stringToSign = implode("\n", [
            'TC3-HMAC-SHA256', $timestamp,
            "{$date}/{$service}/tc3_request",
            hash('sha256', $canonicalRequest),
        ]);

        $secretDate    = hash_hmac('sha256', $date, 'TC3' . $this->secretKey, true);
        $secretService = hash_hmac('sha256', $service, $secretDate, true);
        $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
        $signature     = hash_hmac('sha256', $stringToSign, $secretSigning);

        $response = Http::withHeaders([
            'Authorization'  => "TC3-HMAC-SHA256 Credential={$this->secretId}/{$date}/{$service}/tc3_request, SignedHeaders=content-type;host;x-tc-action, Signature={$signature}",
            'Content-Type'   => 'application/json',
            'Host'           => $endpoint,
            'X-TC-Action'    => $action,
            'X-TC-Version'   => $version,
            'X-TC-Region'    => $this->region,
            'X-TC-Timestamp' => (string) $timestamp,
        ])->withBody($body, 'application/json')->post("https://{$endpoint}/");

        return $response->json() ?? [];
    }
}
