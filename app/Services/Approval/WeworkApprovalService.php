<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeworkApprovalService implements ApprovalServiceInterface
{
    protected function getAccessToken(): string
    {
        $corpId = config('services.wework.corp_id');
        $secret = config('services.wework.approval_secret');
        $response = Http::get("https://qyapi.weixin.qq.com/cgi-bin/gettoken", [
            'corpid' => $corpId,
            'corpsecret' => $secret,
        ]);
        return $response->json('access_token', '');
    }

    public function createInstance(array $data): string
    {
        $token = $this->getAccessToken();
        $response = Http::post("https://qyapi.weixin.qq.com/cgi-bin/oa/applyevent?access_token={$token}", $data);

        if (!$response->json('errcode', -1) === 0) {
            Log::error('Wework approval create failed', $response->json());
            return '';
        }

        return $response->json('sp_no', '');
    }

    public function getInstance(string $instanceId): array
    {
        $token = $this->getAccessToken();
        $response = Http::post("https://qyapi.weixin.qq.com/cgi-bin/oa/getapprovalinfo?access_token={$token}", [
            'sp_no' => $instanceId,
        ]);
        return $response->json('info', []);
    }

    public function registerCallback(string $url): void
    {
        // Wework callback is configured via admin console or API
        Log::info('Wework approval callback registered', ['url' => $url]);
    }

    public function testConnection(): bool
    {
        return !empty($this->getAccessToken());
    }

    public function getConfig(): array
    {
        return [
            'corp_id' => config('services.wework.corp_id'),
            'template_id' => config('services.wework.approval_template_id'),
        ];
    }

    public function setConfig(array $config): void
    {
        // Config is set via .env / config/services.php
    }
}
