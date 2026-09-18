<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseRequest;
use App\Services\Procurement\ProcurementService;
use Illuminate\Http\Request;

class PurchaseRequestController extends BaseApiController
{
    public function __construct(protected ProcurementService $procurementService) {}

    public function index(Request $request)
    {
        $purchaseRequests = $this->procurementService->listPurchaseRequests($request->all(), $request->per_page ?? 15);
        return $this->paginated($purchaseRequests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:191',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');
        $validated['requested_by'] = $request->user()->id;

        $purchaseRequest = $this->procurementService->createPurchaseRequest($validated);
        return $this->success($purchaseRequest->load(['requester', 'vendor']), '采购申请创建成功', 201);
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        return $this->success($purchaseRequest->load(['requester', 'vendor', 'approver']));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $purchaseRequest = $this->procurementService->updatePurchaseRequest($purchaseRequest, $validated);
        return $this->success($purchaseRequest->load(['requester', 'vendor']), '更新成功');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $this->procurementService->deletePurchaseRequest($purchaseRequest);
        return $this->success(null, '删除成功');
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest = $this->procurementService->approvePurchaseRequest(
                $purchaseRequest,
                $request->user()->id
            );
            return $this->success($purchaseRequest->load(['requester', 'vendor', 'approver']), '审批通过');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        try {
            $purchaseRequest = $this->procurementService->rejectPurchaseRequest(
                $purchaseRequest,
                $request->user()->id
            );
            return $this->success($purchaseRequest->load(['requester', 'vendor', 'approver']), '已拒绝');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
