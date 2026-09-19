<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeishuApprovalService implements ApprovalServiceInterface
{
    protected function getTenantAccessToken(): string
    {
        $appId = config('services.feishu.app_id');
        $appSecret = config('services.feishu.app_secret');
        $response = Http::post('https://open.feishu.cn/open-apis/auth/v3/tenant_access_token/internal', [
            'app_id' => $appId,
            'app_secret' => $appSecret,
        ]);
        return $response->json('tenant_access_token', '');
    }

    public function createInstance(array $data): string
    {
        $token = $this->getTenantAccessToken();
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->post('https://open.feishu.cn/open-apis/approval/v4/instance/create', $data);

        if ($response->json('code', -1) !== 0) {
            Log::error('Feishu approval create failed', $response->json());
            return '';
        }

        return $response->json('data.instance_code', '');
    }

    public function getInstance(string $instanceId): array
    {
        $token = $this->getTenantAccessToken();
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->get("https://open.feishu.cn/open-apis/approval/v4/instance/{$instanceId}");

        return $response->json('data', []);
    }

    public function registerCallback(string $url): void
    {
        Log::info('Feishu approval callback registered', ['url' => $url]);
    }

    public function testConnection(): bool
    {
        return !empty($this->getTenantAccessToken());
    }

    public function getConfig(): array
    {
        return [
            'app_id' => config('services.feishu.app_id'),
            'approval_code' => config('services.feishu.approval_code'),
        ];
    }

    public function setConfig(array $config): void
    {
        // Config is set via .env / config/services.php
    }
}
