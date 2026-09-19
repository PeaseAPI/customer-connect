<?php

namespace App\Services\PhoneVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 腾讯云号码认证Provider
 *
 * API文档: https://cloud.tencent.com/document/product/1007 (人脸核身-手机号三要素)
 * 认证方式: TC3-HMAC-SHA256 (SecretId+SecretKey)
 * Endpoint: faceid.tencentcloudapi.com | API版本: 2018-03-01
 *
 * 核心API: PhoneVerification (手机号三要素核验)
 * 说明: 腾讯云不支持"姓名+手机号"二要素, 仅提供三要素核验。
 */
class TencentPhoneVerify implements PhoneVerifyInterface
{
    private string $secretId;
    private string $secretKey;
    private string $region;
    private bool $enabled;

    public function __construct()
    {
        $this->secretId  = config('services.phone_verify.tencent.secret_id', '');
        $this->secretKey = config('services.phone_verify.tencent.secret_key', '');
        $this->region    = config('services.phone_verify.tencent.region', 'ap-guangzhou');
        $this->enabled   = config('services.phone_verify.tencent.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->secretId) && !empty($this->secretKey);
    }

    public function getProviderName(): string
    {
        return 'tencent';
    }

    public function twoFactorVerify(string $name, string $phone, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->errorResult('腾讯云号码认证不支持二要素核验(需身份证号)');
    }

    public function threeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'result' => 'DISABLED', 'carrier' => '', 'record' => null, 'message' => '腾讯云号码认证服务未启用'];
        }

        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('姓名格式错误');
        }
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return $this->errorResult('手机号格式错误');
        }
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('身份证号格式错误');
        }

        try {
            $payload = ['Name' => $name, 'Phone' => $phone, 'IdCard' => $idNumber];
            $response = $this->tc3Request('PhoneVerification', $payload);

            $error = $response['Response']['Error'] ?? null;
            if ($error) {
                Log::error('Tencent phone verify error', ['code' => $error['Code'] ?? '', 'message' => $error['Message'] ?? '']);
                return $this->errorResult($error['Message'] ?? '认证服务异常');
            }

            $data    = $response['Response'] ?? [];
            $isMatch = ($data['Result'] ?? '') === 'pass';
            $result  = $isMatch ? 'MATCH' : 'MISMATCH';

            $record = IdentityVerification::create([
                'provider'        => 'tencent',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => 'three_factor',
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => '',
                'request_id'      => $data['RequestId'] ?? '',
                'raw_response'    => $response,
                'verified_at'     => now(),
            ]);

            return [
                'success' => $isMatch,
                'result'  => $result,
                'carrier' => '',
                'record'  => $record,
                'message' => $isMatch ? '认证通过' : ($data['Description'] ?? '认证不一致'),
            ];
        } catch (\Exception $e) {
            Log::error('Tencent phone verify exception', ['message' => $e->getMessage()]);
            return $this->errorResult('认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 腾讯云TC3签名请求(复用人脸核身逻辑)
     */
    private function tc3Request(string $action, array $payload): array
    {
        $service   = 'faceid';
        $version   = '2018-03-01';
        $timestamp = time();
        $date      = gmdate('Y-m-d', $timestamp);
        $body      = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $endpoint  = 'faceid.tencentcloudapi.com';

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

    private function errorResult(string $message): array
    {
        return ['success' => false, 'result' => 'ERROR', 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
