<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (!$company) {
            return $this->error('未关联公司', 404);
        }
        return $this->success($company->load(['subscription.package']));
    }

    public function update(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (!$company) {
            return $this->error('未关联公司', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'short_name' => 'sometimes|string|max:100',
            'logo' => 'sometimes|string|max:500',
            'address' => 'sometimes|string|max:500',
            'industry' => 'sometimes|string|max:100',
            'website' => 'sometimes|url|max:255',
            'description' => 'sometimes|string|max:1000',
            'contact_name' => 'sometimes|string|max:100',
            'contact_phone' => 'sometimes|string|max:20',
            'contact_email' => 'sometimes|email|max:255',
        ]);

        $company->update($validated);
        return $this->success($company->fresh());
    }
}
