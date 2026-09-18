<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEstimateRequestRequest;
use App\Http\Requests\UpdateEstimateRequestRequest;
use App\Http\Requests\ConvertEstimateRequestRequest;
use App\Models\EstimateRequest;
use App\Services\Finance\EstimateRequestService;
use Illuminate\Http\Request;

class EstimateRequestController extends BaseApiController
{
    public function __construct(protected EstimateRequestService $estimateRequestService) {}

    public function index(Request $request)
    {
        $requests = $this->estimateRequestService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($requests);
    }

    public function store(StoreEstimateRequestRequest $request)
    {
        $v = $request->validated();
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->estimateRequestService->create($v)->load(['client', 'estimate', 'creator']), '报价请求创建成功', 201);
    }

    public function show(EstimateRequest $estimateRequest)
    {
        return $this->success($estimateRequest->load(['client', 'estimate', 'creator']));
    }

    public function update(UpdateEstimateRequestRequest $request, EstimateRequest $estimateRequest)
    {
        $v = $request->validated();
        $estimateRequest = $this->estimateRequestService->update($estimateRequest, $v);
        return $this->success($estimateRequest->load(['client', 'estimate', 'creator']), 'Updated successfully');
    }

    public function destroy(EstimateRequest $estimateRequest)
    {
        $this->estimateRequestService->delete($estimateRequest);
        return $this->success(null, 'Deleted successfully');
    }

    public function convert(ConvertEstimateRequestRequest $request, EstimateRequest $estimateRequest)
    {
        $v = $request->validated();
        $estimateRequest = $this->estimateRequestService->convert($estimateRequest, $v['estimate_id']);
        return $this->success($estimateRequest->load(['client', 'estimate', 'creator']), '报价请求已转换为报价');
    }
}
