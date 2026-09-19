<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends BaseApiController
{
    public function __construct(protected CompanyService $companyService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $companies = $this->companyService->list(
            $user->company_id,
            $user->isSuperAdmin(),
            $request->all(),
            $request->per_page ?? 15
        );

        return $this->paginated($companies);
    }

    public function show(Company $company)
    {
        return $this->success($company->load(['package', 'subscription', 'organisationSetting']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:191',
            'company_email' => 'required|email|max:191',
            'company_phone' => 'nullable|string|max:30',
            'status' => 'sometimes|in:active,inactive,suspended,expired',
        ]);

        $company = Company::create($validated);

        return $this->success($company->load(['package', 'subscription']), 'Company created successfully', 201);
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return $this->success(null, 'Company deleted');
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:191',
            'company_email' => 'sometimes|email',
            'company_phone' => 'sometimes|string|max:30',
            // Branding fields are set from the admin company settings page.
            // Files are stored by the web layer; the API only keeps the path.
            'logo' => 'sometimes|nullable|string|max:255',
            'logo_background_color' => 'sometimes|nullable|string|max:20',
            'login_background' => 'sometimes|nullable|string|max:255',
        ]);

        $company = $this->companyService->update($company, $validated);

        return $this->success($company, 'Updated successfully');
    }
}
