<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateInvoiceSettingRequest;
use App\Services\Finance\InvoiceSettingService;
use Illuminate\Http\Request;

class InvoiceSettingController extends BaseApiController
{
    public function __construct(protected InvoiceSettingService $invoiceSettingService) {}

    public function show(Request $request)
    {
        $setting = $this->invoiceSettingService->get($request->user()->company_id);
        return $this->success($setting);
    }

    public function update(UpdateInvoiceSettingRequest $request)
    {
        $v = $request->validated();
        $setting = $this->invoiceSettingService->update($request->user()->company_id, $v);
        return $this->success($setting, '发票设置更新成功');
    }
}
