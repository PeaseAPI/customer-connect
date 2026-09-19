<?php

namespace App\Services\ContentSecurity;

use App\Models\ContentAuditLog;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 阿里云内容安全Provider
 *
 * API文档: https://help.aliyun.com/zh/ims/content-moderation/
 * 认证方式: AK/SK (RPC签名)
 * Endpoint: green.cn-shanghai.aliyuncs.com | API版本: 2022-03-02
 *
 * 核心API:
 * - TextScan  文本审核(同步, 单次最大6000字符)
 * - ImageScan 图片审核(同步)
 * - VideoAsyncScan  视频审核(异步, 回调)
 * - AudioAsyncScan  音频审核(异步, 回调)
 *
 * 响应: {"code":200,"data":[{"code":200,"results":[{"suggestion":"pass|block|review","label":"..."}]}]}
 */
class AliyunContentSecurity implements ContentSecurityInterface
{
    private string $accessKeyId;
    private string $accessKeySecret;
    private string $endpoint;
    private bool $enabled;

    public function __construct()
    {
        $this->accessKeyId     = config('services.content_security.aliyun.access_key_id', '');
        $this->accessKeySecret = config('services.content_security.aliyun.access_key_secret', '');
        $this->endpoint        = config('services.content_security.aliyun.endpoint', 'green.cn-shanghai.aliyuncs.com');
        $this->enabled         = config('services.content_security.aliyun.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->accessKeyId) && !empty($this->accessKeySecret);
    }

    public function getProviderName(): string
    {
        return 'aliyun';
    }

    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '阿里云内容安全服务未启用'];
        }
        if (trim($content) === '') {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容不能为空'];
        }
        if (mb_strlen($content) > 6000) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核内容超长(最大6000字)'];
        }

        try {
            $scenes = $categories ?: ['antispam'];
            $body   = json_encode([
                'bizScenario' => 'kht',
                'serviceParameters' => json_encode([
                    'content' => $content,
                ]),
            ], JSON_UNESCAPED_UNICODE);

            $response = $this->request('TextScan', ['Service' => 'comment_detection'], $body);

            if (($response['code'] ?? 500) !== 200) {
                Log::error('Aliyun text audit error', ['message' => $response['msg'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['msg'] ?? '审核服务异常'];
            }

            $item       = $response['data'][0] ?? [];
            $results    = $item['results'] ?? [];
            $suggestion = $this->mapSuggestion($results[0]['suggestion'] ?? 'review');
            $label      = $results[0]['label'] ?? '';
            $hits       = array_map(fn ($r) => ['label' => $r['label'] ?? '', 'suggestion' => $r['suggestion'] ?? ''], $results);

            $record = ContentAuditLog::create([
                'provider'       => 'aliyun',
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
                'task_id'        => $item['taskId'] ?? $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规: ' . $label : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun text audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '阿里云内容安全服务未启用'];
        }
        if (empty($url) && empty($data)) {
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '必须提供图片URL或data'];
        }

        try {
            $scenes  = $categories ?: ['porn', 'terror', 'ad'];
            $body    = json_encode([
                'bizScenario' => 'kht',
                'serviceParameters' => json_encode(array_filter([
                    'imageUrl'    => $url,
                    'dataId'      => uniqid('kht_img_'),
                ], fn ($v) => $v !== null)),
            ], JSON_UNESCAPED_UNICODE);

            $response = $this->request('ImageSyncScan', ['Service' => 'baselineCheck'], $body);

            if (($response['code'] ?? 500) !== 200) {
                Log::error('Aliyun image audit error', ['message' => $response['msg'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['msg'] ?? '审核服务异常'];
            }

            $item       = $response['data'][0] ?? [];
            $results    = $item['results'] ?? [];
            $suggestion = $this->mapSuggestion($results[0]['suggestion'] ?? 'review');
            $label      = $results[0]['label'] ?? '';
            $hits       = array_map(fn ($r) => ['label' => $r['label'] ?? '', 'suggestion' => $r['suggestion'] ?? '', 'rate' => $r['rate'] ?? 0], $results);

            $record = ContentAuditLog::create([
                'provider'       => 'aliyun',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'image',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => $suggestion,
                'labels'         => $hits,
                'task_id'        => $item['taskId'] ?? $response['requestId'] ?? '',
                'details'        => $response,
            ]);

            return [
                'success'    => $suggestion !== 'BLOCK',
                'suggestion' => $suggestion,
                'record'     => $record,
                'message'    => $suggestion === 'PASS' ? '审核通过' : ($suggestion === 'BLOCK' ? '内容违规: ' . $label : '需人工审核'),
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun image audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '阿里云内容安全服务未启用'];
        }

        try {
            $body = json_encode([
                'bizScenario' => 'kht',
                'serviceParameters' => json_encode([
                    'videoUrl'    => $url,
                    'dataId'      => uniqid('kht_vid_'),
                    'callbackUrl' => $callbackUrl,
                ]),
            ], JSON_UNESCAPED_UNICODE);

            $response = $this->request('VideoAsyncScan', ['Service' => 'videoDetection'], $body);

            if (($response['code'] ?? 500) !== 200) {
                Log::error('Aliyun video audit error', ['message' => $response['msg'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['msg'] ?? '审核服务异常'];
            }

            $item    = $response['data'][0] ?? [];
            $taskId  = $item['taskId'] ?? '';

            $record = ContentAuditLog::create([
                'provider'       => 'aliyun',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'video',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => 'PENDING',
                'labels'         => [],
                'task_id'        => $taskId,
                'details'        => $response,
            ]);

            return [
                'success'    => true,
                'suggestion' => 'PENDING',
                'record'     => $record,
                'message'    => '已提交异步审核,结果将通过回调返回',
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun video audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'suggestion' => 'DISABLED', 'record' => null, 'message' => '阿里云内容安全服务未启用'];
        }

        try {
            $body = json_encode([
                'bizScenario' => 'kht',
                'serviceParameters' => json_encode([
                    'audioUrl'    => $url,
                    'dataId'      => uniqid('kht_aud_'),
                    'callbackUrl' => $callbackUrl,
                ]),
            ], JSON_UNESCAPED_UNICODE);

            $response = $this->request('AudioAsyncScan', ['Service' => 'audio_detection'], $body);

            if (($response['code'] ?? 500) !== 200) {
                Log::error('Aliyun audio audit error', ['message' => $response['msg'] ?? '']);
                return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => $response['msg'] ?? '审核服务异常'];
            }

            $item   = $response['data'][0] ?? [];
            $taskId = $item['taskId'] ?? '';

            $record = ContentAuditLog::create([
                'provider'       => 'aliyun',
                'company_id'     => Context::get('current_company_id'),
                'user_id'        => auth()->id(),
                'auditable_type' => $auditableType,
                'auditable_id'   => $auditableId,
                'content_type'   => 'audio',
                'content_preview' => null,
                'content_url'    => $url,
                'suggestion'     => 'PENDING',
                'labels'         => [],
                'task_id'        => $taskId,
                'details'        => $response,
            ]);

            return [
                'success'    => true,
                'suggestion' => 'PENDING',
                'record'     => $record,
                'message'    => '已提交异步审核,结果将通过回调返回',
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun audio audit exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'suggestion' => 'ERROR', 'record' => null, 'message' => '审核服务异常: ' . $e->getMessage()];
        }
    }

    /**
     * 处理异步审核回调(视频/音频)
     * 阿里云回调格式: [{"code":200,"dataId":"...","taskId":"...","results":[{"suggestion":"pass|block|review","label":"..."}]}]
     */
    public function handleCallback(array $callbackData): ContentAuditLog
    {
        $items = $callbackData[0] ?? $callbackData;
        $taskId = $items['taskId'] ?? '';

        $log = ContentAuditLog::where('provider', 'aliyun')
            ->where('task_id', $taskId)
            ->orderByDesc('created_at')
            ->first();

        if (!$log) {
            throw new \RuntimeException("未找到对应的审核记录: {$taskId}");
        }

        $results    = $items['results'] ?? [];
        $suggestion = $this->mapSuggestion($results[0]['suggestion'] ?? 'review');
        $hits       = array_map(fn ($r) => ['label' => $r['label'] ?? '', 'suggestion' => $r['suggestion'] ?? ''], $results);

        $log->update([
            'suggestion' => $suggestion,
            'labels'     => $hits,
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
     * 阿里云Green API签名请求(ACS3-HMAC-SHA256)
     */
    private function request(string $action, array $query, string $body): array
    {
        $pathMap = [
            'TextScan'       => '/green/text/scan',
            'ImageSyncScan'  => '/green/image/scan',
            'VideoAsyncScan' => '/green/video/asyncscan',
            'AudioAsyncScan' => '/green/audio/asyncscan',
        ];
        $path = $pathMap[$action] ?? '/green/text/scan';

        $accept      = 'application/json';
        $contentType = 'application/json';
        $contentMd5  = base64_encode(md5($body, true));
        $date        = gmdate('D, d M Y H:i:s \G\M\T');
        $nonce       = uniqid('aliyun_', true);

        // 签名串(ACS3格式)
        $stringToSign = "POST\n{$accept}\n{$contentMd5}\n{$contentType}\n{$date}\n"
            . "x-acs-action:{$action}\nx-acs-signature-nonce:{$nonce}\nx-acs-version:2022-03-02\n{$path}";

        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->accessKeySecret, true));

        $response = Http::withHeaders([
            'Accept'                  => $accept,
            'Content-MD5'             => $contentMd5,
            'Content-Type'            => $contentType,
            'Date'                    => $date,
            'Host'                    => $this->endpoint,
            'x-acs-action'            => $action,
            'x-acs-signature-nonce'   => $nonce,
            'x-acs-signature-version' => '1.0',
            'x-acs-version'           => '2022-03-02',
            'Authorization'           => "ACS3-HMAC-SHA256 Credential={$this->accessKeyId},SignedHeaders=accept;content-md5;content-type;date;host;x-acs-action;x-acs-signature-nonce;x-acs-version,Signature={$signature}",
        ])->withBody($body, $contentType)
          ->post("https://{$this->endpoint}{$path}");

        return $response->json() ?? [];
    }
}
