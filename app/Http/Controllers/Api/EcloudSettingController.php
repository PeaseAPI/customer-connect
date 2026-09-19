<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Ecloud\EcloudClient;
use App\Services\Ecloud\PhoneVerifyService;
use App\Services\Ecloud\ContentAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * 移动云集成设置控制器
 * 
 * 管理移动云AK/SK配置、实名认证开关、内容审核开关等
 */
class EcloudSettingController extends BaseApiController
{
    /**
     * 获取移动云集成设置
     */
    public function index()
    {
        return $this->success([
            'access_key' => config('services.ecloud.access_key', ''),
            'gateway_url' => config('services.ecloud.gateway_url', 'https://gateway.ecloud.10086.cn/api'),
            'timeout' => config('services.ecloud.timeout', 30),
            'phone_verify_enabled' => config('services.ecloud.phone_verify_enabled', false),
            'content_audit_enabled' => config('services.ecloud.content_audit_enabled', false),
            'audit_categories' => config('services.ecloud.audit_categories', ['politics', 'violence', 'porn', 'contraband', 'ad', 'abuse']),
            'audit_block_action' => config('services.ecloud.audit_block_action', 'block'),
            'audit_fail_open' => config('services.ecloud.audit_fail_open', true),
            'audit_callback_url' => config('services.ecloud.audit_callback_url', ''),
        ]);
    }

    /**
     * 更新移动云集成设置
     */
    public function update(Request $request)
    {
        $request->validate([
            'access_key' => 'nullable|string|max:100',
            'secret_key' => 'nullable|string|max:100',
            'gateway_url' => 'nullable|url|max:200',
            'timeout' => 'nullable|integer|min:5|max:120',
            'phone_verify_enabled' => 'nullable|boolean',
            'content_audit_enabled' => 'nullable|boolean',
            'audit_categories' => 'nullable|array',
            'audit_categories.*' => 'string|in:politics,violence,porn,contraband,ad,abuse,spam',
            'audit_block_action' => 'nullable|string|in:block,flag',
            'audit_fail_open' => 'nullable|boolean',
            'audit_callback_url' => 'nullable|url|max:500',
        ]);

        // 更新环境配置（实际项目中应存入company_settings表）
        $settings = $request->only([
            'access_key', 'gateway_url', 'timeout',
            'phone_verify_enabled', 'content_audit_enabled',
            'audit_categories', 'audit_block_action',
            'audit_fail_open', 'audit_callback_url',
        ]);

        // secret_key 单独处理（加密存储）
        if ($request->filled('secret_key')) {
            $settings['secret_key'] = $request->input('secret_key');
        }

        // 存入company_settings (使用现有的CompanySetting机制)
        $companyId = app('App\Services\ContextService')->getCompanyId();
        $company = \App\Models\Company::find($companyId);

        if ($company) {
            $existing = $company->settings ?? [];
            $existing['ecloud'] = array_merge($existing['ecloud'] ?? [], $settings);
            $company->update(['settings' => $existing]);
        }

        return $this->success($settings, '设置已更新');
    }

    /**
     * 测试移动云连接
     */
    public function testConnection()
    {
        $client = app(EcloudClient::class);
        $connected = $client->testConnection();

        return $this->success([
            'connected' => $connected,
            'config' => $client->getConfig(),
        ], $connected ? '连接成功' : '连接失败');
    }

    /**
     * 测试实名认证
     */
    public function testPhoneVerify(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
        ]);

        $service = app(PhoneVerifyService::class);
        $result = $service->twoFactorVerify(
            $request->input('name'),
            $request->input('phone'),
        );

        return $this->success($result, $result['message']);
    }

    /**
     * 测试内容审核
     */
    public function testContentAudit(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $service = app(ContentAuditService::class);
        $result = $service->auditText($request->input('content'));

        return $this->success($result, $result['message']);
    }
}
