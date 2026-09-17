<?php

namespace App\Http\Controllers\Api;

use App\Models\Tax;
use App\Services\Finance\TaxService;
use Illuminate\Http\Request;

class TaxController extends BaseApiController
{
    public function __construct(protected TaxService $taxService) {}

    public function index(Request $request)
    {
        $taxes = $this->taxService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($taxes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_name' => 'required|string|max:191',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'include_in_total' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->taxService->create($validated), '税率创建成功', 201);
    }

    public function show(Tax $tax)
    {
        return $this->success($tax);
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'tax_name' => 'sometimes|string|max:191',
            'tax_percent' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'include_in_total' => 'nullable|boolean',
        ]);

        $tax = $this->taxService->update($tax, $validated);

        return $this->success($tax, '更新成功');
    }

    public function destroy(Tax $tax)
    {
        $this->taxService->delete($tax);

        return $this->success(null, '删除成功');
    }
}
