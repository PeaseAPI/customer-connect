<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Subscription;
use App\Models\Company;
use App\Models\SubscriptionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::with(['company', 'package']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:subscription_packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'in:active,trial,expired,cancelled',
        ]);

        $subscription = Subscription::create($validated);
        return $this->success($subscription->load(['company', 'package']), '订阅创建成功', 201);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        return $this->success($subscription->load(['company', 'package', 'payments']));
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'sometimes|exists:subscription_packages,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:active,trial,expired,cancelled',
        ]);

        $subscription->update($validated);

        // 如果升级套餐，同步更新公司的限制
        if (isset($validated['package_id'])) {
            $package = SubscriptionPackage::find($validated['package_id']);
            if ($package) {
                $subscription->company->update([
                    'max_users' => $package->max_users,
                ]);
            }
        }

        return $this->success($subscription->fresh()->load(['company', 'package']));
    }

    public function renew(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'end_date' => 'required|date|after:' . $subscription->end_date->toDateString(),
        ]);

        $subscription->update([
            'end_date' => $validated['end_date'],
            'status' => 'active',
        ]);

        return $this->success($subscription->fresh(), '订阅续费成功');
    }
}
