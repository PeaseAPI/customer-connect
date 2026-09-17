<?php

namespace App\Services\SocialLogin;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WechatLoginService implements SocialLoginInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_id' => config('services.wechat_open.app_id'),
            'app_secret' => config('services.wechat_open.app_secret'),
            'redirect_url' => config('services.wechat_open.redirect_url', url('/api/auth/wechat/callback')),
        ];
    }

    public function getRedirectUrl(): string
    {
        $params = http_build_query([
            'appid' => $this->config['app_id'],
            'redirect_uri' => $this->config['redirect_url'],
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => csrf_token(),
        ]);
        return 'https://open.weixin.qq.com/connect/qrconnect?' . $params . '#wechat_redirect';
    }

    public function handleCallback(string $code): array
    {
        try {
            $response = Http::get('https://api.weixin.qq.com/sns/oauth2/access_token', [
                'appid' => $this->config['app_id'],
                'secret' => $this->config['app_secret'],
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);
            $tokenData = $response->json();
            if (isset($tokenData['errcode'])) {
                return ['success' => false, 'error' => $tokenData['errmsg']];
            }
            $userResponse = Http::get('https://api.weixin.qq.com/sns/userinfo', [
                'access_token' => $tokenData['access_token'],
                'openid' => $tokenData['openid'],
                'lang' => 'zh_CN',
            ]);
            $userData = $userResponse->json();
            return [
                'success' => true,
                'openid' => $tokenData['openid'],
                'unionid' => $tokenData['unionid'] ?? null,
                'nickname' => $userData['nickname'] ?? '',
                'avatar' => $userData['headimgurl'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('微信登录异常', ['error' => $e->getMessage()]);
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
