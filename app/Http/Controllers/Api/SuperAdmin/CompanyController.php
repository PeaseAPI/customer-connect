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
                $q->where('company_name', 'like', "%{$request->keyword}%")
                  ->orWhere('company_email', 'like', "%{$request->keyword}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $companies = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($companies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:30',
            'status' => 'sometimes|in:active,inactive,suspended,expired',
            'license_type' => 'nullable|string|max:50',
            'license_expire_on' => 'nullable|date',
        ]);

        $company = Company::create($validated);

        return $this->success($company->load('subscription.package'), 'Company created successfully', 201);
    }

    public function show(Company $company): JsonResponse
    {
        $company->load(['subscription.package', 'users']);
        return $this->success($company);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'company_email' => 'sometimes|email|max:255',
            'company_phone' => 'sometimes|string|max:30',
            'status' => 'sometimes|in:active,inactive,suspended,expired',
            'license_type' => 'sometimes|string|max:50',
            'license_expire_on' => 'sometimes|date',
        ]);

        $company->update($validated);
        return $this->success($company->fresh());
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Inactive]);
        return $this->success(null, 'Company disabled');
    }

    public function activate(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Active]);
        return $this->success(null, 'Company enabled');
    }

    public function suspend(Company $company): JsonResponse
    {
        $company->update(['status' => CompanyStatus::Suspended]);
        return $this->success(null, 'Company suspended');
    }
}
