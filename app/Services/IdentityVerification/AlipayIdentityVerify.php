<?php

namespace App\Services\IdentityVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 支付宝身份认证Provider
 *
 * API文档: https://open.alipay.com/api/detail?code=I1080300001000044085
 * 认证方式: RSA2签名 (app_id + private_key)
 * Gateway: https://openapi.alipay.com/gateway.do
 *
 * 核心API:
 * - alipay.user.certify.open.initialize  初始化认证
 * - alipay.user.certify.open.certify     获取认证URL(需用户跳转)
 * - alipay.user.certify.open.query       查询认证结果
 *
 * 说明: 支付宝实人认证为"异步跳转"模式 - 初始化后需引导用户跳转支付宝完成活体检测,
 *       之后查询认证结果。本实现以"身份证二要素核验"等价能力对齐(通过 openapi 直查),
 *       完整人脸核身需前端 SDK 配合。
 */
class AlipayIdentityVerify implements IdentityVerifyInterface
{
    private string $appId;
    private string $privateKey;
    private string $publicKey;
    private string $gateway;
    private bool $enabled;

    public function __construct()
    {
        $this->appId      = config('services.identity_verify.alipay.app_id', '');
        $this->privateKey = config('services.identity_verify.alipay.private_key', '');
        $this->publicKey  = config('services.identity_verify.alipay.public_key', '');
        $this->gateway    = config('services.identity_verify.alipay.gateway', 'https://openapi.alipay.com/gateway.do');
        $this->enabled    = config('services.identity_verify.alipay.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->appId) && !empty($this->privateKey);
    }

    public function getProviderName(): string
    {
        return 'alipay';
    }

    /**
     * 支付宝身份证二要素核验(通过 fund_certify 或认证结果查询)
     * 说明: 支付宝开放平台不直接提供姓名+身份证号的"同步二要素核验"API,
     *       此实现走 initialize + query 的组合流程, 结果取 certify_status。
     */
    public function idCardVerify(string $name, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify($name, '', $idNumber, $verifiableType, $verifiableId);
    }

    public function phoneThreeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        return $this->verify($name, $phone, $idNumber, $verifiableType, $verifiableId);
    }

    public function bankCardVerify(string $name, string $bankCard, ?string $idNumber = null, ?string $phone = null, ?string $verifiableType = null, ?int $verifiableId = null): array
    {
        // 支付宝开放平台暂不提供银行卡核验API
        return $this->errorResult('UNSUPPORTED', '支付宝身份认证暂不支持银行卡核验');
    }


    /**
     * 调用支付宝 initialize + query 流程
     */
    private function verify(string $name, string $phone, string $idNumber, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return $this->errorResult('DISABLED', '支付宝身份认证服务未启用');
        }

        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('ERROR', '姓名格式错误');
        }
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('ERROR', '身份证号格式错误');
        }

        try {
            $outerOrderNo = 'KHT_' . date('YmdHis') . '_' . uniqid();

            // 1. 初始化认证
            $initResult = $this->apiCall('alipay.user.certify.open.initialize', [
                'outer_order_no' => $outerOrderNo,
                'biz_code'       => 'FACE',
                'identity_type'  => 'CERT_INFO',
                'cert_type'      => 'IDENTITY_CARD',
                'cert_name'      => $name,
                'cert_no'        => $idNumber,
                'mobile'         => $phone,
            ]);

            if (($initResult['alipay_user_certify_open_initialize_response']['code'] ?? '') !== '10000') {
                $subMsg = $initResult['alipay_user_certify_open_initialize_response']['sub_msg'] ?? '初始化失败';
                Log::error('Alipay certify init error', ['response' => $initResult]);
                return $this->errorResult('ERROR', $subMsg);
            }

            $certifyId = $initResult['alipay_user_certify_open_initialize_response']['certify_id'] ?? '';

            // 2. 查询认证结果(部分场景下提交即出结果)
            $queryResult = $this->apiCall('alipay.user.certify.open.query', [
                'certify_id' => $certifyId,
            ]);

            $queryResp = $queryResult['alipay_user_certify_open_query_response'] ?? [];
            $passed    = ($queryResp['passed'] ?? '') === 'T';
            $result    = $passed ? 'MATCH' : 'MISMATCH';

            $record = IdentityVerification::create([
                'provider'        => 'alipay',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => $phone !== '' ? 'three_factor' : 'two_factor',
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => '',
                'request_id'      => $certifyId,
                'raw_response'    => ['init' => $initResult, 'query' => $queryResult],
                'verified_at'     => now(),
            ]);

            return [
                'success' => $passed,
                'result'  => $result,
                'carrier' => '',
                'record'  => $record,
                'message' => $passed ? '认证通过' : '认证不一致或待完成',
                'certify_url' => $certifyId ? $this->buildCertifyUrl($certifyId) : '',
            ];
        } catch (\Exception $e) {
            Log::error('Alipay certify exception', ['message' => $e->getMessage()]);
            return $this->errorResult('ERROR', '认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 支付宝网关API调用(RSA2签名)
     */
    private function apiCall(string $method, array $bizContent): array
    {
        $params = [
            'app_id'      => $this->appId,
            'method'      => $method,
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE),
        ];

        $params['sign'] = $this->sign($params);

        $response = Http::asForm()->timeout(30)->post($this->gateway, $params);
        return $response->json() ?? [];
    }

    /**
     * RSA2签名
     */
    private function sign(array $params): string
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $pairs[] = "{$k}={$v}";
            }
        }
        $data = implode('&', $pairs);

        $key = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->privateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * 构建认证跳转URL(前端需引导用户跳转完成活体检测)
     */
    private function buildCertifyUrl(string $certifyId): string
    {
        $params = [
            'app_id'    => $this->appId,
            'method'    => 'alipay.user.certify.open.certify',
            'format'    => 'JSON',
            'charset'   => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0',
            'biz_content' => json_encode(['certify_id' => $certifyId]),
        ];
        $params['sign'] = $this->sign($params);
        return $this->gateway . '?' . http_build_query($params);
    }

    private function errorResult(string $result, string $message): array
    {
        return ['success' => false, 'result' => $result, 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
