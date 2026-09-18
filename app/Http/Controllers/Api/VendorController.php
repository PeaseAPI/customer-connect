<?php

namespace App\Http\Controllers\Api;

use App\Models\Vendor;
use App\Services\Procurement\ProcurementService;
use Illuminate\Http\Request;

class VendorController extends BaseApiController
{
    public function __construct(protected ProcurementService $procurementService) {}

    public function index(Request $request)
    {
        $vendors = $this->procurementService->listVendors($request->all(), $request->per_page ?? 15);
        return $this->paginated($vendors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $vendor = $this->procurementService->createVendor($validated);
        return $this->success($vendor, 'Vendor created successfully', 201);
    }

    public function show(Vendor $vendor)
    {
        return $this->success($vendor);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'vendor_name' => 'sometimes|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $vendor = $this->procurementService->updateVendor($vendor, $validated);
        return $this->success($vendor, 'Updated successfully');
    }

    public function destroy(Vendor $vendor)
    {
        $this->procurementService->deleteVendor($vendor);
        return $this->success(null, 'Deleted successfully');
    }
}
