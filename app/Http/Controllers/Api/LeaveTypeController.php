<?php

namespace App\Http\Controllers\Api;

use App\Models\LeaveType;
use App\Services\HRM\LeaveTypeService;
use Illuminate\Http\Request;

class LeaveTypeController extends BaseApiController
{
    public function __construct(protected LeaveTypeService $leaveTypeService) {}

    public function index(Request $request)
    {
        $types = $this->leaveTypeService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:191',
            'is_paid' => 'sometimes|boolean',
            'paid_leaves' => 'sometimes|integer',
            'carry_forward' => 'sometimes|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->leaveTypeService->create($validated), '假期类型创建成功', 201);
    }

    public function show(LeaveType $leaveType)
    {
        return $this->success($leaveType);
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'type_name' => 'sometimes|string|max:191',
            'is_paid' => 'sometimes|boolean',
            'paid_leaves' => 'sometimes|integer',
            'carry_forward' => 'sometimes|boolean',
        ]);

        $leaveType = $this->leaveTypeService->update($leaveType, $validated);

        return $this->success($leaveType, '更新成功');
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->leaveTypeService->delete($leaveType);

        return $this->success(null, '删除成功');
    }
}

