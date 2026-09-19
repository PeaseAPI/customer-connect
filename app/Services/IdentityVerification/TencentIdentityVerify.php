<?php

namespace App\Services\IdentityVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 腾讯云人脸核身Provider
 *
 * API文档: https://cloud.tencent.com/document/product/1007
 * 认证方式: TC3-HMAC-SHA256 (SecretId+SecretKey)
 * Endpoint: faceid.tencentcloudapi.com | API版本: 2018-03-01
 *
 * 核心API:
 * - IdCardVerify          二要素核验(姓名+身份证号)
 * - PhoneVerification     手机号三要素核验
 * - BankCard2EVerification  银行卡二要素
 * - BankCard4EVerification  银行卡四要素
 */
class TencentIdentityVerify implements IdentityVerifyInterface
{
    private string $secretId;
    private string $secretKey;
    private string $endpoint;
    private string $region;
    private bool $enabled;

    public function __construct()
    {
        $this->secretId  = config('services.identity_verify.tencent.secret_id', '');
        $this->secretKey = config('services.identity_verify.tencent.secret_key', '');
        $this->endpoint  = 'faceid.tencentcloudapi.com';
        $this->region    = config('services.identity_verify.tencent.region', 'ap-guangzhou');
        $this->enabled   = config('services.identity_verify.tencent.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->secretId) && !empty($this->secretKey);
    }

    public function getProviderName(): string
    {
        return 'tencent';
    }

    public function idCardVerify(string $name, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('IdCardVerify', $name, '', $idNumber, '', $verifiableType, $verifiableId);
    }

    public function phoneThreeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('PhoneVerification', $name, $phone, $idNumber, '', $verifiableType, $verifiableId);
    }

    public function bankCardVerify(string $name, string $bankCard, ?string $idNumber = null, ?string $phone = null, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        $action = (!empty($idNumber) && !empty($phone)) ? 'BankCard4EVerification' : 'BankCard2EVerification';
        return $this->verify($action, $name, $phone ?? '', $idNumber, $bankCard, $verifiableType, $verifiableId);
    }


    /**
     * 统一调用腾讯云TC3签名API
     */
    private function verify(string $action, string $name, string $phone, ?string $idNumber, string $bankCard, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return $this->errorResult('DISABLED', '腾讯云人脸核身服务未启用');
        }

        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('ERROR', '姓名格式错误');
        }
        if ($idNumber && !preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('ERROR', '身份证号格式错误');
        }

        try {
            // 组装腾讯云请求参数(帕斯卡命名)
            $payload = array_filter([
                'Name'    => $name,
                'IdCard'  => $idNumber,
                'Phone'   => $phone,
                'BankCard'=> $bankCard,
            ], fn ($v) => $v !== null && $v !== '');

            $response = $this->tc3Request($action, $payload);

            $code = $response['Response']['Error'] ?? null;
            if ($code) {
                Log::error('Tencent identity verify error', [
                    'action' => $action,
                    'code'   => $code['Code'] ?? '',
                    'message'=> $code['Message'] ?? '',
                ]);
                return $this->errorResult('ERROR', $code['Message'] ?? '认证服务异常');
            }

            $data = $response['Response'] ?? [];
            // 腾讯云返回: {"Response":{"Result":"pass","Description":"一致","RequestId":"..."}}
            $result = ($data['Result'] ?? '') === 'pass' ? 'MATCH' : 'MISMATCH';

            $record = IdentityVerification::create([
                'provider'        => 'tencent',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => str_contains($action, 'BankCard') ? 'bank_card'
                    : (str_contains($action, 'Phone') ? 'three_factor' : 'two_factor'),
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => '',
                'request_id'      => $data['RequestId'] ?? '',
                'raw_response'    => $response,
                'verified_at'     => now(),
            ]);

            $isMatch = $result === 'MATCH';
            return [
                'success' => $isMatch,
                'result'  => $result,
                'carrier' => '',
                'record'  => $record,
                'message' => $isMatch ? '认证通过' : ($data['Description'] ?? '认证不一致'),
            ];
        } catch (\Exception $e) {
            Log::error('Tencent identity verify exception', ['action' => $action, 'message' => $e->getMessage()]);
            return $this->errorResult('ERROR', '认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 腾讯云TC3-HMAC-SHA256签名请求
     */
    private function tc3Request(string $action, array $payload): array
    {
        $service    = 'faceid';
        $version    = '2018-03-01';
        $timestamp  = time();
        $date       = gmdate('Y-m-d', $timestamp);
        $body       = json_encode($payload, JSON_UNESCAPED_UNICODE);

        // 1. 拼接规范请求串
        $canonicalRequest = implode("\n", [
            'POST',
            '/',
            '',
            "content-type:application/json\nhost:{$this->endpoint}\nx-tc-action:" . strtolower($action),
            'content-type;host;x-tc-action',
            hash('sha256', $body),
        ]);

        // 2. 拼接待签名串
        $stringToSign = implode("\n", [
            'TC3-HMAC-SHA256',
            $timestamp,
            "{$date}/{$service}/tc3_request",
            hash('sha256', $canonicalRequest),
        ]);

        // 3. 计算签名
        $secretDate  = hash_hmac('sha256', $date, 'TC3' . $this->secretKey, true);
        $secretService = hash_hmac('sha256', $service, $secretDate, true);
        $secretSigning = hash_hmac('sha256', 'tc3_request', $secretService, true);
        $signature   = hash_hmac('sha256', $stringToSign, $secretSigning);

        $authorization = "TC3-HMAC-SHA256 "
            . "Credential={$this->secretId}/{$date}/{$service}/tc3_request, "
            . "SignedHeaders=content-type;host;x-tc-action, "
            . "Signature={$signature}";

        $response = Http::withHeaders([
            'Authorization'   => $authorization,
            'Content-Type'    => 'application/json',
            'Host'            => $this->endpoint,
            'X-TC-Action'     => $action,
            'X-TC-Version'    => $version,
            'X-TC-Region'     => $this->region,
            'X-TC-Timestamp'  => (string) $timestamp,
        ])->withBody($body, 'application/json')
          ->post("https://{$this->endpoint}/");

        return $response->json() ?? [];
    }

    private function errorResult(string $result, string $message): array
    {
        return ['success' => false, 'result' => $result, 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
