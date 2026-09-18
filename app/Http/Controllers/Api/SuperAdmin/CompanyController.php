<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Enums\CompanyStatus;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Company::with(['subscription.package']);

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->keyword}%")
                  ->orWhere('short_name', 'like', "%{$request->keyword}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($companies);
    }

    public function show(Company $company): JsonResponse
    {
        $company->load(['subscription.package', 'users']);
        return $this->success($company);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'short_name' => 'sometimes|string|max:100',
                        'status' => 'sometimes|in:active,inactive,suspended,expired',
            'max_users' => 'sometimes|integer|min:1',
            'expire_at' => 'sometimes|date',
        ]);

        $company->update($validated);
        return $this->success($company->fresh());
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Inactive]);
        return $this->success(null, '公司已停用');
    }

    public function activate(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Active]);
        return $this->success(null, '公司已启用');
    }

    public function suspend(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Suspended]);
        return $this->success(null, '公司已暂停');
    }
}
