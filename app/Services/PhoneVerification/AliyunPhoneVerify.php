<?php

namespace App\Services\PhoneVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 阿里云号码认证Provider
 *
 * API文档: https://help.aliyun.com/zh/pnvs/
 * 认证方式: AK/SK (RPC签名)
 * Endpoint: dypnsapi.cn-hangzhou.aliyuncs.com | API版本: 2017-05-25
 *
 * 核心API:
 * - PhoneThreeVerification  手机号三要素核验
 * - VerifyPhoneNum          本机号码校验(需前端Token)
 */
class AliyunPhoneVerify implements PhoneVerifyInterface
{
    private string $accessKeyId;
    private string $accessKeySecret;
    private string $endpoint;
    private bool $enabled;

    public function __construct()
    {
        $this->accessKeyId     = config('services.phone_verify.aliyun.access_key_id', '');
        $this->accessKeySecret = config('services.phone_verify.aliyun.access_key_secret', '');
        $this->endpoint        = config('services.phone_verify.aliyun.endpoint', 'dypnsapi.cn-hangzhou.aliyuncs.com');
        $this->enabled         = config('services.phone_verify.aliyun.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->accessKeyId) && !empty($this->accessKeySecret);
    }

    public function getProviderName(): string
    {
        return 'aliyun';
    }

    public function twoFactorVerify(string $name, string $phone, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        // 阿里云PNVS不提供"姓名+手机号"二要素独立API, 建议使用三要素
        return $this->verify('two_factor', $name, $phone, null, $verifiableType, $verifiableId);
    }

    public function threeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('three_factor', $name, $phone, $idNumber, $verifiableType, $verifiableId);
    }


    private function verify(string $type, string $name, string $phone, ?string $idNumber, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'result' => 'DISABLED', 'carrier' => '', 'record' => null, 'message' => '阿里云号码认证服务未启用'];
        }

        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('姓名格式错误');
        }
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return $this->errorResult('手机号格式错误');
        }
        if ($type === 'three_factor' && $idNumber && !preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('身份证号格式错误');
        }

        try {
            $params = array_filter([
                'PhoneNumber' => $phone,
                'Name'        => $name,
                'IdentifyNum' => $idNumber,
            ], fn ($v) => $v !== null && $v !== '');

            $response = $this->request('PhoneThreeVerification', $params);

            $code = (string) ($response['Code'] ?? '-1');
            if ($code !== 'OK') {
                Log::error('Aliyun phone verify error', ['type' => $type, 'code' => $code, 'message' => $response['Message'] ?? '']);
                return $this->errorResult($response['Message'] ?? '认证服务异常');
            }

            $data = $response['Data'] ?? [];
            // Data: {"VerifyResult":"PASS|REJECT|UNKNOWN","Carrier":"CMCC|CUCC|CTCC","VerifyId":"..."}
            $verifyResult = $data['VerifyResult'] ?? 'UNKNOWN';
            $result       = $verifyResult === 'PASS' ? 'MATCH' : ($verifyResult === 'REJECT' ? 'MISMATCH' : 'UNKNOWN');
            $carrier      = $data['Carrier'] ?? '';

            $record = IdentityVerification::create([
                'provider'        => 'aliyun',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => $type,
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => $carrier,
                'request_id'      => $data['VerifyId'] ?? $response['RequestId'] ?? '',
                'raw_response'    => $response,
                'verified_at'     => now(),
            ]);

            $msgMap = ['MATCH' => '认证通过', 'MISMATCH' => '认证不一致', 'UNKNOWN' => '查无此人'];
            return [
                'success' => $result === 'MATCH',
                'result'  => $result,
                'carrier' => $carrier,
                'record'  => $record,
                'message' => $msgMap[$result] ?? '未知结果',
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun phone verify exception', ['type' => $type, 'message' => $e->getMessage()]);
            return $this->errorResult('认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 阿里云RPC签名请求
     */
    private function request(string $action, array $params): array
    {
        $publicParams = array_merge($params, [
            'Action'           => $action,
            'Version'          => '2017-05-25',
            'Format'           => 'JSON',
            'AccessKeyId'      => $this->accessKeyId,
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => uniqid('aliyun_', true),
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
        ]);

        ksort($publicParams);
        $query = http_build_query($publicParams, '', '&', PHP_QUERY_RFC3986);
        $stringToSign = 'POST&%2F&' . rawurlencode($query);
        $publicParams['Signature'] = base64_encode(
            hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true)
        );

        $response = Http::asForm()->timeout(30)->post("https://{$this->endpoint}/", $publicParams);
        return $response->json() ?? [];
    }

    private function errorResult(string $message): array
    {
        return ['success' => false, 'result' => 'ERROR', 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
