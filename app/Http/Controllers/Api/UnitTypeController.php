<?php

namespace App\Http\Controllers\Api;

use App\Models\UnitType;
use App\Services\Product\UnitTypeService;
use Illuminate\Http\Request;

class UnitTypeController extends BaseApiController
{
    public function __construct(protected UnitTypeService $unitTypeService) {}

    public function index(Request $request)
    {
        $units = $this->unitTypeService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($units);
    }

        public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_type' => 'required|string|max:191|unique:unit_types,unit_type,NULL,id,company_id,' . $request->attributes->get('company_id'),
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->unitTypeService->create($validated), '计量单位创建成功', 201);
    }

    public function show(UnitType $unitType)
    {
        return $this->success($unitType);
    }

        public function update(Request $request, UnitType $unitType)
    {
        $validated = $request->validate([
            'unit_type' => 'sometimes|string|max:191|unique:unit_types,unit_type,' . $unitType->id . ',id,company_id,' . $request->attributes->get('company_id'),
        ]);

        $unitType = $this->unitTypeService->update($unitType, $validated);

        return $this->success($unitType, 'Updated successfully');
    }

    public function destroy(UnitType $unitType)
    {
        $this->unitTypeService->delete($unitType);

        return $this->success(null, 'Deleted successfully');
    }
}
