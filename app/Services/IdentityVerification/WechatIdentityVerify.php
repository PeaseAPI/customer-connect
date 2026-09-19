<?php

namespace App\Services\IdentityVerification;

use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 微信实名认证Provider
 *
 * API文档: https://pay.weixin.qq.com/doc/v2/merchant/4011986977
 * 认证方式: 微信支付商户签名 (mch_id + api_key)
 *
 * 核心流程(灰度中):
 * 1. OAuth2.0授权获取code → 2. code换access_token → 3. 实名校验(姓名+身份证+access_token)
 *
 * 说明: 微信实名认证依赖前端完成OAuth授权, 后端提供 access_token 换取与校验能力。
 *       二要素核验需微信侧配合(前端授权后回调)。
 */
class WechatIdentityVerify implements IdentityVerifyInterface
{
    private string $appId;
    private string $mchId;
    private string $apiKey;
    private bool $enabled;

    public function __construct()
    {
        $this->appId   = config('services.identity_verify.wechat.app_id', '');
        $this->mchId   = config('services.identity_verify.wechat.mch_id', '');
        $this->apiKey  = config('services.identity_verify.wechat.api_key', '');
        $this->enabled = config('services.identity_verify.wechat.enabled', false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->appId) && !empty($this->mchId) && !empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'wechat';
    }

    /**
     * 身份证二要素核验
     * 微信实名校验需 access_token (来自OAuth授权), 此处支持传入 access_token
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
        return $this->errorResult('UNSUPPORTED', '微信实名认证暂不支持银行卡核验');
    }

    /**
     * OAuth code 换 access_token (供前端授权流程使用)
     */
    public function exchangeAccessToken(string $code): array
    {
        return \Illuminate\Support\Facades\Http::timeout(30)
            ->get('https://api.weixin.qq.com/sns/oauth2/access_token', [
                'appid'      => $this->appId,
                'secret'     => $this->apiKey,
                'code'       => $code,
                'grant_type' => 'authorization_code',
            ])->json() ?? [];
    }


    /**
     * 执行实名核验(需要前端OAuth授权后回调携带open_access_token)
     */
    private function verify(string $name, string $phone, string $idNumber, ?string $verifiableType, ?int $verifiableId): array
    {
        if (!$this->isEnabled()) {
            return $this->errorResult('DISABLED', '微信实名认证服务未启用');
        }

        if (empty($name) || mb_strlen($name) > 50) {
            return $this->errorResult('ERROR', '姓名格式错误');
        }
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return $this->errorResult('ERROR', '身份证号格式错误');
        }

        try {
            // 微信实名认证灰度中,标准流程需前端OAuth授权
            // 这里记录核验请求,实际校验通过微信支付商户接口(realname)完成
            $checkUrl = 'https://api.mch.weixin.qq.com/mch/customs/realnamecheck';

            $params = [
                'appid'      => $this->appId,
                'mch_id'     => $this->mchId,
                'nonce_str'  => uniqid('wx_', true),
                'name'       => $name,
                'id_card'    => $idNumber,
                'phone'      => $phone,
            ];
            $params['sign'] = $this->md5Sign($params);

            $response = Http::asForm()->timeout(30)->post($checkUrl, $params);
            $xml      = simplexml_load_string($response->body());
            $json     = json_decode(json_encode((array) $xml), true) ?: [];

            $returnCode = $json['return_code'] ?? 'FAIL';
            $resultCode = $json['result_code'] ?? 'FAIL';

            $isMatch = ($returnCode === 'SUCCESS' && $resultCode === 'SUCCESS'
                && ($json['identical'] ?? 'N') === 'Y');

            $result = $isMatch ? 'MATCH' : 'MISMATCH';

            $record = IdentityVerification::create([
                'provider'        => 'wechat',
                'company_id'      => Context::get('current_company_id'),
                'user_id'         => auth()->id(),
                'verifiable_type' => $verifiableType,
                'verifiable_id'   => $verifiableId,
                'type'            => $phone ? 'three_factor' : 'two_factor',
                'name'            => $name,
                'phone'           => $phone,
                'id_number'       => $idNumber,
                'result'          => $result,
                'carrier'         => '',
                'request_id'      => $json['transaction_id'] ?? $json['out_trade_no'] ?? '',
                'raw_response'    => $json,
                'verified_at'     => now(),
            ]);

            return [
                'success' => $isMatch,
                'result'  => $result,
                'carrier' => '',
                'record'  => $record,
                'message' => $isMatch ? '认证通过' : ($json['err_code_des'] ?? '认证不一致'),
            ];
        } catch (\Exception $e) {
            Log::error('Wechat identity verify exception', ['message' => $e->getMessage()]);
            return $this->errorResult('ERROR', '认证服务异常: ' . $e->getMessage());
        }
    }

    /**
     * 微信支付MD5签名
     */
    private function md5Sign(array $params): string
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $pairs[] = "{$k}={$v}";
            }
        }
        $string = implode('&', $pairs) . '&key=' . $this->apiKey;
        return strtoupper(md5($string));
    }

    private function errorResult(string $result, string $message): array
    {
        return ['success' => false, 'result' => $result, 'carrier' => '', 'record' => null, 'message' => $message];
    }
}
