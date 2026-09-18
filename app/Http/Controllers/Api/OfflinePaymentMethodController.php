<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreOfflinePaymentMethodRequest;
use App\Http\Requests\UpdateOfflinePaymentMethodRequest;
use App\Models\OfflinePaymentMethod;
use App\Services\Finance\OfflinePaymentMethodService;
use Illuminate\Http\Request;

class OfflinePaymentMethodController extends BaseApiController
{
    public function __construct(protected OfflinePaymentMethodService $offlinePaymentMethodService) {}

    public function index(Request $request)
    {
        $methods = $this->offlinePaymentMethodService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($methods);
    }

    public function store(StoreOfflinePaymentMethodRequest $request)
    {
        $v = $request->validated();
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->offlinePaymentMethodService->create($v), '线下支付方式创建成功', 201);
    }

    public function show(OfflinePaymentMethod $offlinePaymentMethod)
    {
        return $this->success($offlinePaymentMethod);
    }

    public function update(UpdateOfflinePaymentMethodRequest $request, OfflinePaymentMethod $offlinePaymentMethod)
    {
        $v = $request->validated();
        $offlinePaymentMethod = $this->offlinePaymentMethodService->update($offlinePaymentMethod, $v);
        return $this->success($offlinePaymentMethod, 'Updated successfully');
    }

    public function destroy(OfflinePaymentMethod $offlinePaymentMethod)
    {
        $this->offlinePaymentMethodService->delete($offlinePaymentMethod);
        return $this->success(null, 'Deleted successfully');
    }
}
