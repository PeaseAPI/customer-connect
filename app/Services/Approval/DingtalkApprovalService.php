<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DingtalkApprovalService implements ApprovalServiceInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_key' => config('services.dingtalk.app_key'),
            'app_secret' => config('services.dingtalk.app_secret'),
            'corp_id' => config('services.dingtalk.corp_id'),
        ];
    }

    public function createInstance(array $data): string
    {
        try {
            $token = $this->getAccessToken();
            $response = Http::withToken($token)
                ->post('https://api.dingtalk.com/v1.0/workflow/processInstances', [
                    'originatorUserId' => $data['originator_user_id'],
                    'processCode' => $data['process_code'],
                    'formComponentValues' => $data['form_values'] ?? [],
                ]);
            return $response->json('instanceId', '');
        } catch (\Exception $e) {
            Log::error('钉钉创建审批实例失败', ['error' => $e->getMessage()]);
            return '';
        }
    }

    public function getInstance(string $instanceId): array
    {
        try {
            $token = $this->getAccessToken();
            return Http::withToken($token)
                ->get("https://api.dingtalk.com/v1.0/workflow/processInstances/{$instanceId}")
                ->json();
        } catch (\Exception $e) {
            Log::error('钉钉获取审批实例失败', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function registerCallback(string $url): void
    {
        // 钉钉回调在开放平台配置
    }

    public function testConnection(): bool
    {
        return !empty($this->config['app_key']) && !empty($this->config['app_secret']);
    }

    public function getConfig(): array
    {
        return collect($this->config)->except(['app_secret'])->toArray();
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    private function getAccessToken(): string
    {
        return Cache::remember('dingtalk_access_token', 7000, function () {
            $response = Http::post('https://api.dingtalk.com/v1.0/oauth2/accessToken', [
                'appKey' => $this->config['app_key'],
                'appSecret' => $this->config['app_secret'],
            ]);
            return $response->json('accessToken', '');
        });
    }
}
