<?php

namespace App\Services\Ecloud;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 移动云API客户端 - AK/SK + HMAC-SHA256签名认证
 * 
 * 产品文档:
 * - 手机号实名认证: https://ecloud.10086.cn/op-help-center/doc/outline/32923
 * - 内容审核: https://ecloud.10086.cn/op-help-center/doc/outline/41497
 */
class EcloudClient
{
    private string $accessKey;
    private string $secretKey;
    private string $baseUrl;
    private int $timeout;
    private int $retryTimes;

    public function __construct()
    {
        $this->accessKey = config('services.ecloud.access_key', '');
        $this->secretKey = config('services.ecloud.secret_key', '');
        $this->baseUrl = config('services.ecloud.gateway_url', 'https://gateway.ecloud.10086.cn/api');
        $this->timeout = config('services.ecloud.timeout', 30);
        $this->retryTimes = config('services.ecloud.retry_times', 2);
    }

    /**
     * 检查是否已配置
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessKey) && !empty($this->secretKey);
    }

    /**
     * 发送GET请求
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, $query);
    }

    /**
     * 发送POST请求
     */
    public function post(string $uri, array $body = []): array
    {
        return $this->request('POST', $uri, [], $body);
    }

    /**
     * 通用请求方法
     */
    private function request(string $method, string $uri, array $query = [], array $body = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('移动云服务未配置，请先设置 AccessKey 和 SecretKey');
        }

        $timestamp = time();
        $nonce = uniqid('ecloud_', true);
        $signature = $this->computeSignature($method, $uri, $timestamp, $nonce, $body);

        $url = $this->baseUrl . $uri;

        $headers = [
            'x-ecloud-access-key' => $this->accessKey,
            'x-ecloud-signature' => $signature,
            'x-ecloud-timestamp' => (string) $timestamp,
            'x-ecloud-nonce' => $nonce,
            'Content-Type' => 'application/json',
        ];

        $lastException = null;

        for ($attempt = 0; $attempt <= $this->retryTimes; $attempt++) {
            try {
                $http = Http::timeout($this->timeout)->withHeaders($headers);

                if ($method === 'GET') {
                    $response = $http->get($url, $query);
                } else {
                    $response = $http->post($url, $body);
                }

                if ($response->successful()) {
                    $result = $response->json();

                    if (($result['code'] ?? null) === '0' || ($result['code'] ?? null) === 0) {
                        return $result['data'] ?? $result;
                    }

                    // 业务错误
                    Log::warning('Ecloud API business error', [
                        'uri' => $uri,
                        'code' => $result['code'] ?? 'unknown',
                        'message' => $result['message'] ?? '',
                        'request_id' => $result['requestId'] ?? '',
                    ]);

                    return $result;
                }

                // HTTP错误，可重试
                Log::warning('Ecloud API HTTP error', [
                    'uri' => $uri,
                    'status' => $response->status(),
                    'attempt' => $attempt + 1,
                ]);

                $lastException = new \RuntimeException(
                    "移动云API请求失败: HTTP {$response->status()}",
                    $response->status()
                );
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('Ecloud API connection error', [
                    'uri' => $uri,
                    'attempt' => $attempt + 1,
                    'message' => $e->getMessage(),
                ]);
                $lastException = $e;
            }
        }

        throw $lastException ?? new \RuntimeException('移动云API请求失败');
    }

    /**
     * 计算HMAC-SHA256签名
     * 
     * StringToSign = Method + "\n" + URI + "\n" + Timestamp + "\n" + Nonce + "\n" + Body
     * Signature = Base64(HMAC-SHA256(SecretKey, StringToSign))
     */
    private function computeSignature(string $method, string $uri, int $timestamp, string $nonce, array $body = []): string
    {
        $bodyStr = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_UNICODE);

        $stringToSign = implode("\n", [
            $method,
            $uri,
            (string) $timestamp,
            $nonce,
            $bodyStr,
        ]);

        $signature = base64_encode(
            hash_hmac('sha256', $stringToSign, $this->secretKey, true)
        );

        return $signature;
    }

    /**
     * 测试连接（使用二要素认证接口进行测试调用）
     */
    public function testConnection(): bool
    {
        try {
            // 尝试用测试参数调用，期望返回参数错误(30001)而非认证错误(10002/10003)
            $result = $this->post('/phoneverify/v1/two-factor', [
                'name' => '连接测试',
                'phone' => '10000000000',
            ]);

            $code = $result['code'] ?? -1;

            // code=0 表示认证配置正确且调用成功
            // code=30001/30002 表示参数错误，但认证通过
            return $code === '0' || $code === 0 || in_array($code, ['30001', '30002', 30001, 30002]);
        } catch (\Exception $e) {
            Log::error('Ecloud connection test failed', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 获取配置
     */
    public function getConfig(): array
    {
        return [
            'access_key' => $this->accessKey ? substr($this->accessKey, 0, 4) . '****' : '',
            'gateway_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'configured' => $this->isConfigured(),
        ];
    }
}
