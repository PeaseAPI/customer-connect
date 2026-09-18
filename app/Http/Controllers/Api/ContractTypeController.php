<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractType;
use App\Services\Contract\ContractTypeService;
use Illuminate\Http\Request;

class ContractTypeController extends BaseApiController
{
    public function __construct(protected ContractTypeService $contractTypeService) {}

    public function index(Request $request)
    {
        $types = $this->contractTypeService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->contractTypeService->create($validated), 'Contract type created successfully', 201);
    }

    public function show(ContractType $contractType)
    {
        return $this->success($contractType);
    }

    public function update(Request $request, ContractType $contractType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
        ]);

        $contractType = $this->contractTypeService->update($contractType, $validated);

        return $this->success($contractType, 'Updated successfully');
    }

    public function destroy(ContractType $contractType)
    {
        $this->contractTypeService->delete($contractType);

        return $this->success(null, 'Deleted successfully');
    }
}
