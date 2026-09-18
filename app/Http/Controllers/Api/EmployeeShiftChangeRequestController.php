<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
use App\Models\EmployeeShiftChangeRequest;
use App\Services\HRM\EmployeeShiftChangeRequestService;
use Illuminate\Http\Request;

class EmployeeShiftChangeRequestController extends BaseApiController
{
    public function __construct(protected EmployeeShiftChangeRequestService $shiftChangeRequestService) {}

    public function index(Request $request)
    {
        $requests = $this->shiftChangeRequestService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'current_shift_id' => 'nullable|exists:shifts,id',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

                $validated['status'] = ApprovalStatus::Pending;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->shiftChangeRequestService->create($validated)->load(['user', 'shift', 'currentShift']),
            'Shift change request created successfully',
            201
        );
    }

    public function show(EmployeeShiftChangeRequest $shiftChangeRequest)
    {
        return $this->success($shiftChangeRequest->load(['user', 'shift', 'currentShift', 'approver']));
    }

    public function approve(Request $request, EmployeeShiftChangeRequest $shiftChangeRequest)
    {
        $shiftChangeRequest = $this->shiftChangeRequestService->approve($shiftChangeRequest, $request->user()->id);

        return $this->success($shiftChangeRequest->load(['user', 'shift', 'approver']), 'Shift change request approved');
    }

    public function reject(Request $request, EmployeeShiftChangeRequest $shiftChangeRequest)
    {
        $shiftChangeRequest = $this->shiftChangeRequestService->reject($shiftChangeRequest, $request->user()->id);

        return $this->success($shiftChangeRequest->load(['user', 'shift', 'approver']), 'Shift change request rejected');
    }

    public function destroy(EmployeeShiftChangeRequest $shiftChangeRequest)
    {
        $this->shiftChangeRequestService->delete($shiftChangeRequest);

        return $this->success(null, 'Deleted successfully');
    }
}
