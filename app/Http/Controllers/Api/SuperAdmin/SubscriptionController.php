<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Subscription;
use App\Models\Company;
use App\Models\Package;
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
            'package_id' => 'required|exists:packages,id',
            'ends_at' => 'required|date',
            'trial_ends_at' => 'nullable|date',
            'status' => 'in:active,trial,expired,canceled',
        ]);

        $subscription = Subscription::create($validated);
        return $this->success($subscription->load(['company', 'package']), 'Subscription created successfully', 201);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        return $this->success($subscription->load(['company', 'package', 'payments']));
    }

        public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'sometimes|exists:packages,id',
            'ends_at' => 'sometimes|date',
            'trial_ends_at' => 'nullable|date',
            'status' => 'sometimes|in:active,trial,expired,canceled',
        ]);

        $subscription->update($validated);

        // 如果变更套餐，同步更新公司的当前套餐
        if (isset($validated['package_id'])) {
            $subscription->company->update([
                'package_id' => $validated['package_id'],
            ]);
        }

        return $this->success($subscription->fresh()->load(['company', 'package']));
    }

    public function renew(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'ends_at' => 'required|date|after:' . ($subscription->ends_at?->toDateString() ?? now()->toDateString()),
        ]);

        $subscription->update([
            'ends_at' => $validated['ends_at'],
            'status' => SubscriptionStatus::Active,
        ]);

        return $this->success($subscription->fresh(), 'Subscription renewed successfully');
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $subscription->update(['status' => SubscriptionStatus::Canceled]);

        return $this->success(null, 'Subscription canceled');
    }
}
