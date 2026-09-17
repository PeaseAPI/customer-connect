<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SubscriptionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $packages = SubscriptionPackage::orderBy('sort_order')
            ->orderBy('price')
            ->paginate($request->per_page ?? 20);

        return $this->success($packages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subscription_packages',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_users' => 'required|integer|min:1',
            'max_storage_mb' => 'nullable|integer',
            'modules' => 'required|array',
            'modules.*' => 'string',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $package = SubscriptionPackage::create($validated);
        return $this->success($package, '套餐创建成功', 201);
    }

    public function show(SubscriptionPackage $package): JsonResponse
    {
        return $this->success($package);
    }

    public function update(Request $request, SubscriptionPackage $package): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:subscription_packages,code,' . $package->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'billing_cycle' => 'sometimes|in:monthly,yearly',
            'max_users' => 'sometimes|integer|min:1',
            'max_storage_mb' => 'nullable|integer',
            'modules' => 'sometimes|array',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);
        return $this->success($package->fresh());
    }

    public function destroy(SubscriptionPackage $package): JsonResponse
    {
        $package->update(['is_active' => false]);
        return $this->success(null, '套餐已停用');
    }
}
