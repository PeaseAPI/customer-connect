<?php

namespace App\Http\Controllers\Api;

use App\Services\Company\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class SettingController extends BaseApiController
{
    public function __construct(protected SettingService $settingService) {}

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
}
