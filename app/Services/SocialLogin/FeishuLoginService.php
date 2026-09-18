<?php

namespace App\Services\SocialLogin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeishuLoginService implements SocialLoginInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_id' => config('services.feishu.app_id'),
            'app_secret' => config('services.feishu.app_secret'),
            'redirect_url' => config('services.feishu.redirect_url', url('/api/auth/feishu/callback')),
        ];
    }

    public function getRedirectUrl(): string
    {
        $params = http_build_query([
            'app_id' => $this->config['app_id'],
            'redirect_uri' => $this->config['redirect_url'],
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);
        return 'https://open.feishu.cn/open-apis/authen/v1/authorize?' . $params;
    }

    public function handleCallback(string $code): array
    {
        try {
            $tokenResp = Http::post('https://open.feishu.cn/open-apis/auth/v3/app_access_token/internal', [
                'app_id' => $this->config['app_id'],
                'app_secret' => $this->config['app_secret'],
            ]);
            $appAccessToken = $tokenResp->json('app_access_token', '');
            $userTokenResp = Http::withToken($appAccessToken)
                ->post('https://open.feishu.cn/open-apis/authen/v1/oidc/access_token', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ]);
            $userTokenData = $userTokenResp->json('data', []);
            $userResp = Http::withToken($userTokenData['access_token'] ?? '')
                ->get('https://open.feishu.cn/open-apis/authen/v1/user_info');
            $userData = $userResp->json('data', []);
            return [
                'success' => true,
                'open_id' => $userData['open_id'] ?? '',
                'union_id' => $userData['union_id'] ?? '',
                'name' => $userData['name'] ?? '',
                'avatar' => $userData['avatar_url'] ?? '',
                'mobile' => $userData['mobile'] ?? '',
                'email' => $userData['email'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('Feishu login error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function testConnection(): bool
    {
        return !empty($this->config['app_id']) && !empty($this->config['app_secret']);
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
