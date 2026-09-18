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

        return $this->success($employee->load(['employeeDetail.department', 'employeeDetail.designation', 'roles']), 'Updated successfully');
    }

        public function destroy(User $employee)
    {
        $this->employeeService->delete($employee);
        return $this->success(null, 'Deleted successfully');
    }

    /**
     * 邮件邀请员工
     */
    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:191',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'role' => 'nullable|in:employee,manager',
        ]);

        $companyId = $request->attributes->get('company_id');
        $inviterName = $request->user()->name;
        $companyName = $request->attributes->get('company_name', '');

        // 生成邀请令牌
        $token = \Illuminate\Support\Str::random(40);
        \Illuminate\Support\Facades\Cache::put("employee_invite:{$token}", [
            'email' => $validated['email'],
            'name' => $validated['name'],
            'company_id' => $companyId,
            'department_id' => $validated['department_id'] ?? null,
            'designation_id' => $validated['designation_id'] ?? null,
            'role' => $validated['role'] ?? 'employee',
            'invited_by' => $request->user()->id,
        ], now()->addDays(7));

        // 发送邀请邮件
        \Illuminate\Support\Facades\Mail::to($validated['email'])->queue(
            new \App\Mail\EmployeeInvitationMail(
                $validated['name'],
                $inviterName,
                $companyName,
                $token
            )
        );

        return $this->success(['token' => $token], '邀请已发送', 201);
    }

    /**
     * 员工批量导入（CSV/Excel）
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->store('imports');
        $companyId = $request->attributes->get('company_id');

        \App\Jobs\ImportDataJob::dispatch(
            new \App\Imports\EmployeeImport($companyId),
            $path,
            $request->user()->id
        );

        return $this->success(null, '导入任务已提交，完成后将通知您');
    }
}
