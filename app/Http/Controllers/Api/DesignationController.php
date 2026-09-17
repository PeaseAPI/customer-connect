<?php

namespace App\Http\Controllers\Api;

use App\Models\Designation;
use App\Services\HRM\DesignationService;
use Illuminate\Http\Request;

class DesignationController extends BaseApiController
{
    public function __construct(protected DesignationService $designationService) {}

    public function index(Request $request)
    {
        $designations = $this->designationService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($designations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'designation_name' => 'required|string|max:191',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->designationService->create($validated),
            '职位创建成功',
            201
        );
    }

    public function show(Designation $designation)
    {
        return $this->success($designation->load(['company']));
    }

    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'designation_name' => 'sometimes|string|max:191',
        ]);

        $designation = $this->designationService->update($designation, $validated);

        return $this->success($designation, '更新成功');
    }

    public function destroy(Designation $designation)
    {
        $this->designationService->delete($designation);

        return $this->success(null, '删除成功');
    }
}

