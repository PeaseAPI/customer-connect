<?php

namespace App\Services\SocialLogin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DingtalkLoginService implements SocialLoginInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_key' => config('services.dingtalk.app_key'),
            'app_secret' => config('services.dingtalk.app_secret'),
            'redirect_url' => config('services.dingtalk.redirect_url', url('/api/auth/dingtalk/callback')),
        ];
    }

    public function getRedirectUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->config['app_key'],
            'redirect_uri' => $this->config['redirect_url'],
            'response_type' => 'code',
            'scope' => 'openid',
            'prompt' => 'consent',
            'state' => csrf_token(),
        ]);
        return 'https://login.dingtalk.com/oauth2/auth?' . $params;
    }

    public function handleCallback(string $code): array
    {
        try {
            $response = Http::post('https://api.dingtalk.com/v1.0/oauth2/userAccessToken', [
                'clientId' => $this->config['app_key'],
                'clientSecret' => $this->config['app_secret'],
                'code' => $code,
                'grantType' => 'authorization_code',
            ]);
            $tokenData = $response->json();
            if (isset($tokenData['code'])) {
                return ['success' => false, 'error' => $tokenData['message'] ?? 'Failed to get token'];
            }
            $userResponse = Http::withToken($tokenData['accessToken'])
                ->get('https://api.dingtalk.com/v1.0/contact/users/me');
            $userData = $userResponse->json();
            return [
                'success' => true,
                'openid' => $userData['openId'] ?? '',
                'unionid' => $userData['unionId'] ?? '',
                'nickname' => $userData['nick'] ?? '',
                'avatar' => $userData['avatarUrl'] ?? '',
                'mobile' => $userData['mobile'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('DingTalk login error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
}
