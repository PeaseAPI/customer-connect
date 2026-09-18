<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\HRM\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends BaseApiController
{
    public function __construct(protected EmployeeService $employeeService) {}

    public function index(Request $request)
    {
        $employees = $this->employeeService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $user = $this->employeeService->create($validated);

        return $this->success($user->load('employeeDetail'), '员工创建成功', 201);
    }

    public function show(User $employee)
    {
        return $this->success($employee->load([
            'employeeDetail.department', 'employeeDetail.designation', 'roles',
            'emergencyContacts', 'visas',
        ]));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'mobile' => 'sometimes|string|max:20',
                        'status' => 'sometimes|in:active,deactive',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
        ]);

        $employee = $this->employeeService->update($employee, $validated);

        return $this->success($employee->load(['employeeDetail.department', 'employeeDetail.designation', 'roles']), '更新成功');
    }

    public function destroy(User $employee)
    {
        $this->employeeService->delete($employee);
        return $this->success(null, '删除成功');
    }
}
