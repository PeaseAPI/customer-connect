<?php

namespace App\Services\Ecloud;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

/**
 * 手机号实名认证服务
 * 
 * 支持二要素(姓名+手机号)和三要素(姓名+身份证号+手机号)认证
 * 文档: https://ecloud.10086.cn/op-help-center/doc/outline/32923
 */
class PhoneVerifyService
{
    private EcloudClient $client;

    public function __construct(EcloudClient $client)
    {
        $this->client = $client;
    }

    public function isEnabled(): bool
    {
        return config('services.ecloud.phone_verify_enabled', false)
            && $this->client->isConfigured();
    }

    /**
     * 二要素认证 - 姓名+手机号
     */
    public function twoFactorVerify(
        string $name,
        string $phone,
        ?string $verifiableType = null,
        ?int $verifiableId = null
    ): array {
        return $this->verify('two_factor', $name, $phone, null, $verifiableType, $verifiableId);
    }

    /**
     * 三要素认证 - 姓名+身份证号+手机号
     */
    public function threeFactorVerify(
        string $name,
        string $phone,
        string $idNumber,
        ?string $verifiableType = null,
        ?int $verifiableId = null
    ): array {
        return $this->verify('three_factor', $name, $phone, $idNumber, $verifiableType, $verifiableId);
    }

    /**
     * 执行认证
     */
    private function verify(
        string $type,
        string $name,
        string $phone,
        ?string $idNumber,
        ?string $verifiableType,
        ?int $verifiableId
    ): array {
        if (!$this->isEnabled()) {
            return ['success' => false, 'result' => 'DISABLED', 'carrier' => '', 'record' => null, 'message' => '手机号实名认证服务未启用'];
        }

        // 参数验证
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

            $params = array_filter([
                'name' => $name,
                'phone' => $phone,
                'idNumber' => $idNumber,
            ]);

            $response = $this->client->post($uri, $params);
            $code = $response['code'] ?? -1;

            if ($code !== '0' && $code !== 0) {
                Log::error('Phone verify API error', ['type' => $type, 'code' => $code, 'message' => $response['message'] ?? '']);
                return $this->errorResult($response['message'] ?? '认证服务异常');
            }

            $data = $response['data'] ?? $response;
            $result = $data['result'] ?? 'UNKNOWN';
            $carrier = $data['carrier'] ?? '';
            $requestId = $response['requestId'] ?? '';

            $record = IdentityVerification::create([
                'company_id' => Context::get('current_company_id'),
                'user_id' => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id' => $verifiableId,
                'type' => $type,
                'name' => $name,
                'phone' => $phone,
                'id_number' => $idNumber,
                'result' => $result,
                'carrier' => $carrier,
                'request_id' => $requestId,
                'raw_response' => $response,
                'verified_at' => now(),
            ]);

            $msgMap = ['MATCH' => '认证通过', 'MISMATCH' => '认证不一致', 'UNKNOWN' => '查无此人'];

            return [
                'success' => $result === 'MATCH',
                'result' => $result,
                'carrier' => $carrier,
                'record' => $record,
                'message' => $msgMap[$result] ?? '未知结果',
            ];
        } catch (\Exception $e) {
            Log::error('Phone verify exception', ['type' => $type, 'message' => $e->getMessage()]);
            return $this->errorResult('认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 批量认证
     */
    public function batchVerify(array $items, ?string $callbackUrl = null): array
    {
        if (!$this->isEnabled()) {
            return ['batchId' => '', 'totalCount' => 0, 'status' => 'FAILED', 'message' => '服务未启用'];
        }
        if (count($items) > 100) {
            return ['batchId' => '', 'totalCount' => 0, 'status' => 'FAILED', 'message' => '单次批量不超过100条'];
        }

        $params = ['items' => $items];
        if ($callbackUrl) {
            $params['callbackUrl'] = $callbackUrl;
        }

        $response = $this->client->post('/phoneverify/v1/batch', $params);

        return [
            'batchId' => $response['batchId'] ?? '',
            'totalCount' => $response['totalCount'] ?? count($items),
            'status' => $response['status'] ?? 'PROCESSING',
        ];
    }

    /**
     * 查询批量认证结果
     */
    public function getBatchResult(string $batchId): array
    {
        return $this->client->get("/phoneverify/v1/batch/{$batchId}");
    }

    /**
     * 查询单次认证结果
     */
    public function getVerifyResult(string $requestId): array
    {
        return $this->client->get("/phoneverify/v1/result/{$requestId}");
    }

    private function errorResult(string $message): array
    {
        return ['success' => false, 'result' => 'ERROR', 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
