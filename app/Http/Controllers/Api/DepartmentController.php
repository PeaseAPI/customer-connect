<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use App\Services\HRM\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends BaseApiController
{
    public function __construct(protected DepartmentService $departmentService) {}

    public function index(Request $request)
    {
        $departments = $this->departmentService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($departments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:191',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->departmentService->create($validated),
            '部门创建成功',
            201
        );
    }

    public function show(Department $department)
    {
        return $this->success($department->load(['company']));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name' => 'sometimes|string|max:191',
        ]);

        $department = $this->departmentService->update($department, $validated);

        return $this->success($department, '更新成功');
    }

    public function destroy(Department $department)
    {
        $this->departmentService->delete($department);
        return $this->success(null, '删除成功');
    }
}
