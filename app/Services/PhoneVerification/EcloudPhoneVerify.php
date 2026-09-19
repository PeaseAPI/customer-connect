<?php

namespace App\Services\PhoneVerification;

use App\Models\IdentityVerification;
use App\Services\Ecloud\EcloudClient;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

/**
 * 移动云号码认证Provider（适配原有PhoneVerifyService逻辑）
 *
 * 文档: https://ecloud.10086.cn/op-help-center/doc/outline/32923
 * 认证方式: AK/SK + HMAC-SHA256
 * API: /phoneverify/v1/two-factor | /phoneverify/v1/three-factor
 */
class EcloudPhoneVerify implements PhoneVerifyInterface
{
    private EcloudClient $client;

    public function __construct(?EcloudClient $client = null)
    {
        $this->client = $client ?? new EcloudClient();
    }

    public function isEnabled(): bool
    {
        return config('services.phone_verify.ecloud.enabled', false)
            && $this->client->isConfigured();
    }

    public function getProviderName(): string
    {
        return 'ecloud';
    }

    public function twoFactorVerify(string $name, string $phone, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('two_factor', $name, $phone, null, $verifiableType, $verifiableId);
    }

    public function threeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify('three_factor', $name, $phone, $idNumber, $verifiableType, $verifiableId);
    }

    private function verify(string $type, string $name, string $phone, ?string $idNumber, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'result' => 'DISABLED', 'carrier' => '', 'record' => null, 'message' => '移动云号码认证服务未启用'];
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
            $uri = ($type === 'two_factor')
                ? '/phoneverify/v1/two-factor'
                : '/phoneverify/v1/three-factor';

            $response = $this->client->post($uri, array_filter([
                'name'     => $name,
                'phone'    => $phone,
                'idNumber' => $idNumber,
            ]));

            $code = $response['code'] ?? -1;
            if ($code !== '0' && $code !== 0) {
                Log::error('Ecloud phone verify API error', ['type' => $type, 'code' => $code, 'message' => $response['message'] ?? '']);
                return $this->errorResult($response['message'] ?? '认证服务异常');
            }

            $data      = $response['data'] ?? $response;
            $result    = $data['result'] ?? 'UNKNOWN';
            $carrier   = $data['carrier'] ?? '';
            $requestId = $response['requestId'] ?? '';

            $record = IdentityVerification::create([
                'provider'        => 'ecloud',
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
                'request_id'      => $requestId,
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
            Log::error('Ecloud phone verify exception', ['type' => $type, 'message' => $e->getMessage()]);
            return $this->errorResult('认证服务异常: ' . $e->getMessage());
        }
    }

    private function errorResult(string $message): array
    {
        return ['success' => false, 'result' => 'ERROR', 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
