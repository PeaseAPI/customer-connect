<?php

namespace App\Http\Controllers\Api;

use App\Services\Notification\NotificationSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingController extends BaseApiController
{
    public function __construct(protected NotificationSettingService $settingService) {}

    /**
     * 获取当前用户通知设置
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->settingService->getUserSettings($request->user()->id, $companyId);
        return $this->success($data);
    }

    /**
     * 更新当前用户通知设置
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'sound_enabled' => 'sometimes|boolean',
            'desktop_enabled' => 'sometimes|boolean',
            'types' => 'nullable|array',
            'types.*' => 'array',
            'types.*.*.enabled' => 'required|boolean',
        ]);

        $companyId = $request->attributes->get('company_id');
        $this->settingService->updateUserSettings($request->user()->id, $companyId, $validated);

        return $this->success(null, 'Notification settings updated');
    }

    /**
     * Admin获取全局通知设置
     */
    public function companySettings(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->settingService->getCompanySettings($companyId);
        return $this->success($data);
    }

    /**
     * Reset user notification settings to default值
     */
    public function reset(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        \App\Models\NotificationSetting::where('user_id', $request->user()->id)
            ->where('company_id', $companyId)
            ->delete();

        return $this->success(null, 'Notification settings reset to defaults');
    }
}
