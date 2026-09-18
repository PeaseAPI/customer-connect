<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
use App\Events\LeaveRequested;
use App\Models\Leave;
use App\Services\HRM\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends BaseApiController
{
    public function __construct(protected LeaveService $leaveService) {}

    public function index(Request $request)
    {
        $leaves = $this->leaveService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($leaves);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'leave_date' => 'required|date',
            'duration' => 'sometimes|in:full,half_first,half_second',
            'reason' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['company_id'] = $request->attributes->get('company_id');
                $validated['status'] = ApprovalStatus::Pending;

        $leave = $this->leaveService->create($validated);

        event(new LeaveRequested($leave));

        return $this->success($leave->load(['user', 'leaveType']), 'Leave request submitted', 201);
    }

    public function show(Leave $leave)
    {
        return $this->success($leave->load(['user', 'leaveType', 'approver']));
    }

    public function update(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'leave_date' => 'sometimes|date',
            'duration' => 'sometimes|in:full,half_first,half_second',
            'reason' => 'nullable|string',
        ]);

        $leave = $this->leaveService->update($leave, $validated);

        return $this->success($leave->load(['user', 'leaveType']), 'Updated successfully');
    }

    public function destroy(Leave $leave)
    {
        $this->leaveService->delete($leave);
        return $this->success(null, 'Deleted successfully');
    }

    public function approve(Leave $leave)
    {
        $leave = $this->leaveService->approve($leave, Auth::id());
        return $this->success(null, '审批通过');
    }

    public function reject(Request $request, Leave $leave)
    {
        $leave = $this->leaveService->reject($leave, Auth::id());
        return $this->success(null, 'Declined');
    }
}
