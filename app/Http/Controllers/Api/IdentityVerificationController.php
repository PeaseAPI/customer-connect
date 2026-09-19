<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\IdentityVerification;
use App\Services\IdentityVerification\IdentityVerifyManager;
use App\Services\PhoneVerification\PhoneVerifyManager;
use Illuminate\Http\Request;

class IdentityVerificationController extends BaseApiController
{
    private PhoneVerifyManager $phoneVerify;
    private IdentityVerifyManager $identityVerify;

    public function __construct(PhoneVerifyManager $phoneVerify, IdentityVerifyManager $identityVerify)
    {
        $this->phoneVerify = $phoneVerify;
        $this->identityVerify = $identityVerify;
    }

    /**
     * 二要素认证（姓名+手机号）- 走当前配置的Provider
     */
    public function twoFactor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
        ]);

        $result = $this->phoneVerify->twoFactorVerify(
            $request->input('name'),
            $request->input('phone'),
            $request->input('verifiable_type'),
            $request->input('verifiable_id'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 三要素认证（姓名+身份证号+手机号）- 走当前配置的Provider
     */
    public function threeFactor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'id_number' => 'required|string|regex:/^\d{17}[\dXx]$/',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
        ]);

        $result = $this->phoneVerify->threeFactorVerify(
            $request->input('name'),
            $request->input('phone'),
            $request->input('id_number'),
            $request->input('verifiable_type'),
            $request->input('verifiable_id'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 批量认证 - 循环调用当前Provider
     */
    public function batchVerify(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1|max:100',
            'items.*.name' => 'required|string|max:50',
            'items.*.phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'items.*.id_number' => 'nullable|string|regex:/^\d{17}[\dXx]$/',
        ]);

        $results = [];
        foreach ($request->input('items') as $item) {
            if (!empty($item['id_number'])) {
                $results[] = $this->phoneVerify->threeFactorVerify(
                    $item['name'],
                    $item['phone'],
                    $item['id_number'],
                );
            } else {
                $results[] = $this->phoneVerify->twoFactorVerify(
                    $item['name'],
                    $item['phone'],
                );
            }
        }

        $matched = collect($results)->where('result', 'MATCH')->count();

        return $this->success([
            'total' => count($results),
            'matched' => $matched,
            'provider' => $this->phoneVerify->getProviderName(),
            'results' => $results,
        ]);
    }

    /**
     * 身份证二要素核验（姓名+身份证号）- 走实人认证渠道(IdentityVerifyManager)
     *
     * 可选 provider 参数临时切换渠道: aliyun|tencent|alipay|wechat
     */
    public function idCard(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'id_number' => 'required|string|regex:/^\d{17}[\dXx]$/',
            'provider' => 'nullable|string|in:aliyun,tencent,alipay,wechat',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
        ]);

        $service = $this->identityVerify->driver($request->input('provider'));
        $result = $service->idCardVerify(
            $request->input('name'),
            $request->input('id_number'),
            $request->input('verifiable_type'),
            $request->input('verifiable_id'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 实人认证三要素核验（姓名+手机号+身份证号）
     *
     * 与 threeFactor(号码认证渠道)的区别: 本端点走实人认证产品
     * (阿里云Id3MetaVerify/腾讯云PhoneVerification/支付宝certify/微信)
     */
    public function phoneThreeFactor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'id_number' => 'required|string|regex:/^\d{17}[\dXx]$/',
            'provider' => 'nullable|string|in:aliyun,tencent,alipay,wechat',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
        ]);

        $service = $this->identityVerify->driver($request->input('provider'));
        $result = $service->phoneThreeFactorVerify(
            $request->input('name'),
            $request->input('phone'),
            $request->input('id_number'),
            $request->input('verifiable_type'),
            $request->input('verifiable_id'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 银行卡核验（姓名+银行卡号, 可选+身份证号/手机号）
     *
     * 说明: alipay/wechat 渠道返回 UNSUPPORTED
     */
    public function bankCard(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'bank_card' => 'required|string|regex:/^\d{10,25}$/',
            'id_number' => 'nullable|string|regex:/^\d{17}[\dXx]$/',
            'phone' => 'nullable|string|regex:/^1[3-9]\d{9}$/',
            'provider' => 'nullable|string|in:aliyun,tencent,alipay,wechat',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
        ]);

        $service = $this->identityVerify->driver($request->input('provider'));
        $result = $service->bankCardVerify(
            $request->input('name'),
            $request->input('bank_card'),
            $request->input('id_number'),
            $request->input('phone'),
            $request->input('verifiable_type'),
            $request->input('verifiable_id'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 查询认证记录列表
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:two_factor,three_factor,bank_card',
            'result' => 'nullable|string|in:MATCH,MISMATCH,UNKNOWN',
            'provider' => 'nullable|string|in:ecloud,aliyun,tencent,alipay,wechat',
            'verifiable_type' => 'nullable|string',
            'verifiable_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = IdentityVerification::query()
            ->with(['user:id,name', 'verifiable']);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('result')) {
            $query->where('result', $request->input('result'));
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }
        if ($request->filled('verifiable_type')) {
            $query->where('verifiable_type', $request->input('verifiable_type'));
        }
        if ($request->filled('verifiable_id')) {
            $query->where('verifiable_id', $request->input('verifiable_id'));
        }

        $records = $query->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return $this->paginated($records);
    }

    /**
     * 查看认证记录详情
     */
    public function show(int $id)
    {
        $record = IdentityVerification::with(['user', 'verifiable'])->findOrFail($id);
        return $this->success($record);
    }

    /**
     * 服务状态检查（号码认证 + 实人认证 双服务）
     */
    public function status()
    {
        $phoneVerify = [
            'current_provider' => $this->phoneVerify->getProviderName(),
            'enabled' => $this->phoneVerify->isEnabled(),
            'providers' => $this->phoneVerify->getProvidersStatus(),
        ];
        $identityVerify = [
            'current_provider' => $this->identityVerify->getProviderName(),
            'enabled' => $this->identityVerify->isEnabled(),
            'providers' => $this->identityVerify->getProvidersStatus(),
        ];

        // 兼容旧版响应结构(旧字段=号码认证服务状态), 新代码请读 phone_verify/identity_verify
        return $this->success(array_merge($phoneVerify, [
            'phone_verify' => $phoneVerify,
            'identity_verify' => $identityVerify,
        ]));
    }
}
