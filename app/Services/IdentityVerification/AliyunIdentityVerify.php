<?php

namespace App\Services\IdentityVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 阿里云实人认证Provider
 *
 * API文档: https://help.aliyun.com/zh/id-verification/
 * 认证方式: AK/SK (RPC签名)
 * Endpoint: idvi.cn-shanghai.aliyuncs.com | API版本: 2022-02-17
 *
 * 核心API:
 * - Id2MetaVerify   二要素核验(姓名+身份证号)
 * - Id3MetaVerify   三要素核验(姓名+身份证号+手机号)
 * - BankMetaVerify  银行卡核验
 */
class AliyunIdentityVerify implements IdentityVerifyInterface
{
    private string $accessKeyId;
    private string $accessKeySecret;
    private string $endpoint;
    private bool $enabled;

    public function __construct()
    {
        $this->accessKeyId     = config('services.identity_verify.aliyun.access_key_id', '');
        $this->accessKeySecret = config('services.identity_verify.aliyun.access_key_secret', '');
        $this->endpoint        = config('services.identity_verify.aliyun.endpoint', 'idvi.cn-shanghai.aliyuncs.com');
        $this->enabled         = config('services.identity_verify.aliyun.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->accessKeyId) && !empty($this->accessKeySecret);
    }

    public function getProviderName(): string
    {
        return 'aliyun';
    }

    public function idCardVerify(string $name, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('id2', $name, '', $idNumber, null, $verifiableType, $verifiableId);
    }

    public function phoneThreeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('id3', $name, $phone, $idNumber, null, $verifiableType, $verifiableId);
    }

    public function bankCardVerify(string $name, string $bankCard, ?string $idNumber = null, ?string $phone = null, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('bank', $name, $phone ?? '', $idNumber, $bankCard, $verifiableType, $verifiableId);
    }


    /**
     * 统一调用阿里云实人认证API
     */
    private function verify(string $scene, string $name, string $phone, ?string $idNumber, ?string $bankCard, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return $this->errorResult('DISABLED', '阿里云实人认证服务未启用');
        }

        // 参数校验
        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('ERROR', '姓名格式错误');
        }
        if ($idNumber && !preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('ERROR', '身份证号格式错误');
        }

        try {
            $action = match ($scene) {
                'id2'   => 'Id2MetaVerify',
                'id3'   => 'Id3MetaVerify',
                'bank'  => 'BankMetaVerify',
                default => 'Id2MetaVerify',
            };

            $params = array_filter([
                'Name'        => $name,
                'IdentifyNum' => $idNumber,
                'Mobile'      => $phone,
                'BankCard'    => $bankCard,
            ], fn ($v) => $v !== null && $v !== '');

            $response = $this->request($action, $params);

            // 阿里云响应: {"Code":"0","Data":{"IsConsistent":1,"BizId":"..."},"RequestId":"..."}
            $code = (string) ($response['Code'] ?? '-1');
            if ($code !== '0') {
                Log::error('Aliyun identity verify error', ['action' => $action, 'code' => $code, 'message' => $response['Message'] ?? '']);
                return $this->errorResult('ERROR', $response['Message'] ?? '认证服务异常');
            }

            $data         = $response['Data'] ?? [];
            $isConsistent = ($data['IsConsistent'] ?? 0) == 1;
            $result       = $isConsistent ? 'MATCH' : 'MISMATCH';

            $record = IdentityVerification::create([
                'provider'        => 'aliyun',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => match ($scene) {
                    'id3'   => 'three_factor',
                    'bank'  => 'bank_card',
                    default => 'two_factor',
                },
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => '',
                'request_id'      => $response['RequestId'] ?? $data['BizId'] ?? '',
                'raw_response'    => $response,
                'verified_at'     => now(),
            ]);

            return [
                'success' => $isConsistent,
                'result'  => $result,
                'carrier' => '',
                'record'  => $record,
                'message' => $isConsistent ? '认证通过' : '认证不一致',
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun identity verify exception', ['scene' => $scene, 'message' => $e->getMessage()]);
            return $this->errorResult('ERROR', '认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 阿里云RPC签名请求
     */
    private function request(string $action, array $params): array
    {
        $publicParams = array_merge($params, [
            'Action'           => $action,
            'Version'          => '2022-02-17',
            'Format'           => 'JSON',
            'AccessKeyId'      => $this->accessKeyId,
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => uniqid('aliyun_', true),
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
        ]);

        $publicParams['Signature'] = $this->computeSignature($publicParams, 'POST');

        $response = Http::asForm()
            ->timeout(30)
            ->post("https://{$this->endpoint}/", $publicParams);

        return $response->json() ?? [];
    }

    /**
     * 阿里云RPC签名(HMAC-SHA1)
     */
    private function computeSignature(array $params, string $method): string
    {
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $stringToSign = $method . '&%2F&' . rawurlencode($query);
        return base64_encode(hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true));
    }

    private function errorResult(string $result, string $message): array
    {
        return ['success' => false, 'result' => $result, 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
