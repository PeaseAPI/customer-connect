<?php

namespace App\Services\Ecloud;

use App\Models\ContentAuditLog;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class ContentAuditService
{
    private EcloudClient $client;

    public function __construct(EcloudClient $client) { $this->client = $client; }

    public function isEnabled(): bool
    {
        return config('services.ecloud.content_audit_enabled', false) && $this->client->isConfigured();
    }

    private function defaultCategories(): array
    {
        return config('services.ecloud.audit_categories', ['politics', 'violence', 'porn', 'contraband', 'ad', 'abuse']);
    }

    public function auditText(string $content, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) return $this->passResult('审核服务未启用');
        if (empty($content)) return $this->passResult('内容为空，跳过审核');
        try {
            $response = $this->client->post('/contentaudit/v1/text', [
                'content' => mb_substr($content, 0, 10000),
                'categories' => $categories ?? $this->defaultCategories(),
            ]);
            return $this->processResponse($response, 'text', $content, $auditableType, $auditableId);
        } catch (\Exception $e) {
            Log::error('Content audit text error', ['message' => $e->getMessage()]);
            return $this->handleAuditError($e);
        }
    }

    public function auditImage(?string $url = null, ?string $data = null, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null): array
    {
        if (!$this->isEnabled()) return $this->passResult('审核服务未启用');
        try {
            $response = $this->client->post('/contentaudit/v1/image', array_filter([
                'url' => $url, 'data' => $data, 'categories' => $categories ?? $this->defaultCategories(),
            ]));
            return $this->processResponse($response, 'image', $url ?? 'base64', $auditableType, $auditableId);
        } catch (\Exception $e) {
            Log::error('Content audit image error', ['message' => $e->getMessage()]);
            return $this->handleAuditError($e);
        }
    }

    public function auditVideo(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null, int $frameInterval = 5): array
    {
        if (!$this->isEnabled()) return $this->passResult('审核服务未启用');
        try {
            $response = $this->client->post('/contentaudit/v1/video', array_filter([
                'url' => $url, 'categories' => $categories ?? $this->defaultCategories(),
                'callbackUrl' => $callbackUrl ?? config('services.ecloud.audit_callback_url'),
                'frameInterval' => $frameInterval,
            ]));
            $taskId = $response['taskId'] ?? '';
            return ['passed' => true, 'suggestion' => 'PENDING', 'taskId' => $taskId,
                'record' => $this->createPendingLog('video', $url, $taskId, $auditableType, $auditableId),
                'message' => '视频审核已提交'];
        } catch (\Exception $e) { return $this->handleAuditError($e); }
    }

    public function auditAudio(string $url, ?string $auditableType = null, ?int $auditableId = null, ?array $categories = null, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) return $this->passResult('审核服务未启用');
        try {
            $response = $this->client->post('/contentaudit/v1/audio', array_filter([
                'url' => $url, 'categories' => $categories ?? $this->defaultCategories(),
                'callbackUrl' => $callbackUrl ?? config('services.ecloud.audit_callback_url'),
            ]));
            $taskId = $response['taskId'] ?? '';
            return ['passed' => true, 'suggestion' => 'PENDING', 'taskId' => $taskId,
                'record' => $this->createPendingLog('audio', $url, $taskId, $auditableType, $auditableId),
                'message' => '音频审核已提交'];
        } catch (\Exception $e) { return $this->handleAuditError($e); }
    }

    public function getAuditResult(string $taskId): array
    {
        return $this->client->get("/contentaudit/v1/result/{$taskId}");
    }


    public function handleCallback(array $callbackData): ContentAuditLog
    {
        $taskId = $callbackData['taskId'] ?? '';
        $record = ContentAuditLog::where('task_id', $taskId)->first();
        if (!$record) {
            $record = ContentAuditLog::create([
                'company_id' => 0, 'user_id' => 0,
                'content_type' => $callbackData['contentType'] ?? 'unknown',
                'content_preview' => '', 'task_id' => $taskId, 'status' => 'COMPLETED',
            ]);
        }
        $record->update([
            'suggestion' => $callbackData['suggestion'] ?? 'UNKNOWN',
            'risk_level' => $callbackData['riskLevel'] ?? 'NONE',
            'labels' => $callbackData['labels'] ?? [],
            'details' => $callbackData['details'] ?? [],
            'status' => 'COMPLETED', 'audited_at' => now(),
        ]);
        if ($record->suggestion === 'BLOCK') {
            Log::warning('Content audit BLOCK', ['taskId' => $taskId, 'labels' => $record->labels]);
        }
        return $record;
    }

    private function processResponse(array $response, string $contentType, string $contentPreview, ?string $auditableType, ?int $auditableId): array
    {
        $code = $response['code'] ?? -1;
        if ($code !== '0' && $code !== 0) {
            return $this->passResult('审核API调用失败，默认放行');
        }
        $data = $response['data'] ?? $response;
        $suggestion = $data['suggestion'] ?? 'PASS';
        $riskLevel = $data['riskLevel'] ?? 'NONE';
        $labels = $data['labels'] ?? [];
        $details = $data['details'] ?? [];
        $taskId = $data['taskId'] ?? '';
        $blockAction = config('services.ecloud.audit_block_action', 'block');
        $passed = $blockAction === 'flag' || $suggestion === 'PASS';
        $record = ContentAuditLog::create([
            'company_id' => Context::get('current_company_id'), 'user_id' => auth()->id(),
            'auditable_type' => $auditableType, 'auditable_id' => $auditableId,
            'content_type' => $contentType, 'content_preview' => mb_substr($contentPreview, 0, 200),
            'task_id' => $taskId, 'suggestion' => $suggestion, 'risk_level' => $riskLevel,
            'labels' => $labels, 'details' => $details, 'status' => 'COMPLETED', 'audited_at' => now(),
        ]);
        $msgMap = ['PASS' => '审核通过', 'BLOCK' => '内容违规，已拦截', 'REVIEW' => '内容存疑，需人工复审'];
        return ['passed' => $passed, 'suggestion' => $suggestion, 'riskLevel' => $riskLevel,
            'labels' => $labels, 'record' => $record, 'message' => $msgMap[$suggestion] ?? '未知审核结果'];
    }

    private function createPendingLog(string $contentType, string $url, string $taskId, ?string $auditableType, ?int $auditableId): ContentAuditLog
    {
        return ContentAuditLog::create([
            'company_id' => Context::get('current_company_id'), 'user_id' => auth()->id(),
            'auditable_type' => $auditableType, 'auditable_id' => $auditableId,
            'content_type' => $contentType, 'content_preview' => $url,
            'task_id' => $taskId, 'suggestion' => 'PENDING', 'risk_level' => 'NONE',
            'labels' => [], 'details' => [], 'status' => 'PROCESSING',
        ]);
    }

    private function passResult(string $reason): array
    {
        return ['passed' => true, 'suggestion' => 'PASS', 'riskLevel' => 'NONE', 'labels' => [], 'record' => null, 'message' => $reason];
    }

    private function handleAuditError(\Exception $e): array
    {
        $failOpen = config('services.ecloud.audit_fail_open', true);
        return ['passed' => $failOpen, 'suggestion' => 'ERROR', 'riskLevel' => 'NONE', 'labels' => [],
            'record' => null, 'message' => '审核服务异常: ' . $e->getMessage() . ($failOpen ? '，默认放行' : '，默认拦截')];
    }
}
