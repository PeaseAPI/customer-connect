<?php

namespace App\Http\Controllers\Api;

use App\Services\Company\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class SettingController extends BaseApiController
{
    public function __construct(protected SettingService $settingService) {}

    /**
     * 获取所有设置
     */
    public function index()
    {
        $companyId = Context::get('current_company_id');
        return $this->success($this->settingService->getAllSettings($companyId));
    }

    public function getOrganisation()
    {
        $companyId = Context::get('current_company_id');
        $setting = $this->settingService->getOrganisation($companyId);

        return $this->success($setting);
    }

    public function updateOrganisation(Request $request)
    {
        $companyId = Context::get('current_company_id');

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:191',
            'company_email' => 'sometimes|email|max:191',
            'company_phone' => 'sometimes|string|max:30',
            'website' => 'nullable|url|max:191',
            'timezone' => 'sometimes|string|max:50',
            'date_format' => 'sometimes|string|max:30',
            'time_format' => 'sometimes|string|max:10',
            'currency_id' => 'nullable|exists:currencies,id',
            'locale' => 'sometimes|string|max:10',
        ]);

        $setting = $this->settingService->updateOrganisation($companyId, $validated);

        return $this->success($setting, '组织设置更新成功');
    }

    /**
     * 获取GDPR设置
     */
    public function getGdpr()
    {
        $companyId = Context::get('current_company_id');
        return $this->success($this->settingService->getGdpr($companyId));
    }

    /**
     * 更新GDPR设置
     */
    public function updateGdpr(Request $request)
    {
        $companyId = Context::get('current_company_id');

        $validated = $request->validate([
            'cookie_consent' => 'sometimes|boolean',
            'cookie_duration' => 'nullable|integer|min:1',
            'privacy_policy_url' => 'nullable|url|max:255',
            'terms_url' => 'nullable|url|max:255',
            'data_retention_days' => 'nullable|integer|min:1',
            'anonymize_on_delete' => 'sometimes|boolean',
        ]);

        $setting = $this->settingService->updateGdpr($companyId, $validated);
        return $this->success($setting, 'GDPR设置更新成功');
    }

    /**
     * 获取通知设置
     */
    public function getNotifications()
    {
        $companyId = Context::get('current_company_id');
        return $this->success($this->settingService->getNotificationSettings($companyId));
    }

    /**
     * 更新通知设置
     */
    public function updateNotification(Request $request, string $type)
    {
        $companyId = Context::get('current_company_id');

        $validated = $request->validate([
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
            'database_enabled' => 'sometimes|boolean',
            'dingtalk_enabled' => 'sometimes|boolean',
            'wework_enabled' => 'sometimes|boolean',
            'feishu_enabled' => 'sometimes|boolean',
        ]);

        $setting = $this->settingService->updateNotificationSetting($companyId, $type, $validated);
        return $this->success($setting, '通知设置更新成功');
    }

    /**
     * 获取模块设置
     */
    public function getModuleSetting(string $module)
    {
        $companyId = Context::get('current_company_id');
        $validModules = ['invoice', 'project', 'task', 'attendance', 'leave', 'timelog', 'contract', 'ticket', 'lead'];

        if (!in_array($module, $validModules)) {
            return $this->error('无效的模块名称', 400);
        }

        return $this->success($this->settingService->getModuleSetting($companyId, $module));
    }

    /**
     * 更新模块设置
     */
    public function updateModuleSetting(Request $request, string $module)
    {
        $companyId = Context::get('current_company_id');
        $validModules = ['invoice', 'project', 'task', 'attendance', 'leave', 'timelog', 'contract', 'ticket', 'lead'];

        if (!in_array($module, $validModules)) {
            return $this->error('无效的模块名称', 400);
        }

        $setting = $this->settingService->updateModuleSetting($companyId, $module, $request->all());
        return $this->success($setting, '模块设置更新成功');
    }
}
