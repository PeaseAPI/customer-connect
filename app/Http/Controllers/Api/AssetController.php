<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use App\Services\Asset\AssetService;
use Illuminate\Http\Request;

class AssetController extends BaseApiController
{
    public function __construct(protected AssetService $assetService) {}

    public function index(Request $request)
    {
        $assets = $this->assetService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($assets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_name' => 'required|string|max:191',
            'asset_code' => 'required|string|unique:assets,asset_code',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'useful_life_months' => 'nullable|integer|min:1',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'sometimes|in:available,allocated,maintenance,retired',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $asset = $this->assetService->create($validated);
        return $this->success($asset->load('allocatedUser'), '资产创建成功', 201);
    }

    public function show(Asset $asset)
    {
        return $this->success($asset->load(['allocatedUser', 'maintenanceRecords']));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_name' => 'sometimes|string|max:191',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'purchase_cost' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'useful_life_months' => 'nullable|integer|min:1',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'sometimes|in:available,allocated,maintenance,retired',
        ]);

        $asset = $this->assetService->update($asset, $validated);
        return $this->success($asset->load('allocatedUser'), '更新成功');
    }

    public function destroy(Asset $asset)
    {
        $this->assetService->delete($asset);
        return $this->success(null, '删除成功');
    }

    /**
     * 分配资产
     */
    public function allocate(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $asset = $this->assetService->allocate($asset, (int) $validated['user_id']);
        return $this->success($asset->load('allocatedUser'), '资产分配成功');
    }

    /**
     * 归还资产
     */
    public function returnAsset(Asset $asset)
    {
        $asset = $this->assetService->returnAsset($asset);
        return $this->success($asset, '资产已归还');
    }

    /**
     * 计算折旧
     */
    public function calculateDepreciation(Asset $asset)
    {
        $result = $this->assetService->calculateDepreciation($asset);
        return $this->success($result);
    }

    /**
     * 添加维护记录
     */
    public function addMaintenanceRecord(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'type' => 'required|in:repair,maintenance,inspection',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'cost' => 'nullable|numeric',
            'vendor' => 'nullable|string|max:191',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:pending,in_progress,completed',
        ]);

        $record = $this->assetService->addMaintenanceRecord($asset, $validated);
        return $this->success($record, '维护记录添加成功', 201);
    }

    /**
     * 维护记录列表
     */
    public function listMaintenanceRecords(Request $request, Asset $asset)
    {
        $records = $this->assetService->listMaintenanceRecords($asset, $request->per_page ?? 15);
        return $this->paginated($records);
    }
}
