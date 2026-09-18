<?php

namespace App\Http\Controllers\Api;

use App\Models\TaxSetting;
use App\Services\Company\TaxSettingService;
use Illuminate\Http\Request;

class TaxSettingController extends BaseApiController
{
    public function __construct(protected TaxSettingService $taxSettingService) {}

    public function index(Request $request)
    {
        $settings = $this->taxSettingService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($settings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_name' => 'required|string|max:191',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->taxSettingService->create($validated),
            'Tax rate created',
            201
        );
    }

    public function show(TaxSetting $taxSetting)
    {
        return $this->success($taxSetting);
    }

    public function update(Request $request, TaxSetting $taxSetting)
    {
        $validated = $request->validate([
            'tax_name' => 'sometimes|string|max:191',
            'tax_percent' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $taxSetting = $this->taxSettingService->update($taxSetting, $validated);

        return $this->success($taxSetting, 'Updated successfully');
    }

    public function destroy(TaxSetting $taxSetting)
    {
        $this->taxSettingService->delete($taxSetting);

        return $this->success(null, 'Deleted successfully');
    }
}
