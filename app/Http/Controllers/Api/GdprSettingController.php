<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateGdprSettingRequest;
use App\Services\Company\GdprSettingService;
use Illuminate\Http\Request;

class GdprSettingController extends BaseApiController
{
    public function __construct(protected GdprSettingService $gdprSettingService) {}

    public function show(Request $request)
    {
        $setting = $this->gdprSettingService->get($request->user()->company_id);
        return $this->success($setting);
    }

    public function update(UpdateGdprSettingRequest $request)
    {
        $v = $request->validated();
        $setting = $this->gdprSettingService->update($request->user()->company_id, $v);
        return $this->success($setting, 'GDPR设置更新成功');
    }
}
