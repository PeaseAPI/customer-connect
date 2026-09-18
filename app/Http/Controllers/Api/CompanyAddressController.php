<?php

namespace App\Http\Controllers\Api;

use App\Models\CompanyAddress;
use App\Services\Company\CompanyAddressService;
use Illuminate\Http\Request;

class CompanyAddressController extends BaseApiController
{
    public function __construct(protected CompanyAddressService $companyAddressService) {}

    public function index(Request $request)
    {
        $addresses = $this->companyAddressService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($addresses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string',
            'is_default' => 'nullable|boolean',
            'tax_number' => 'nullable|string',
            'tax_name' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->user()->company_id;

        return $this->success($this->companyAddressService->create($validated), 'Company address created successfully', 201);
    }

    public function show(CompanyAddress $companyAddress)
    {
        return $this->success($companyAddress);
    }

    public function update(Request $request, CompanyAddress $companyAddress)
    {
        $validated = $request->validate([
            'address' => 'sometimes|string',
            'is_default' => 'nullable|boolean',
            'tax_number' => 'nullable|string',
            'tax_name' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->user()->company_id;
        $companyAddress = $this->companyAddressService->update($companyAddress, $validated);

        return $this->success($companyAddress, 'Updated successfully');
    }

    public function destroy(CompanyAddress $companyAddress)
    {
        $this->companyAddressService->delete($companyAddress);

        return $this->success(null, 'Deleted successfully');
    }
}
