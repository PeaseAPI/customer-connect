<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEmployeeLeaveQuotaRequest;
use App\Http\Requests\UpdateEmployeeLeaveQuotaRequest;
use App\Http\Requests\AdjustLeaveQuotaRequest;
use App\Models\EmployeeLeaveQuota;
use App\Services\HRM\EmployeeLeaveQuotaService;
use Illuminate\Http\Request;

class EmployeeLeaveQuotaController extends BaseApiController
{
    public function __construct(protected EmployeeLeaveQuotaService $leaveQuotaService) {}

    public function index(Request $request, $employeeId)
    {
        $quotas = $this->leaveQuotaService->list($employeeId, $request->per_page ?? 15);
        return $this->paginated($quotas);
    }

    public function store(StoreEmployeeLeaveQuotaRequest $request, $employeeId)
    {
        $v = $request->validated();
        $quota = $this->leaveQuotaService->create($employeeId, $v, $request->user()->id);

        return $this->success($quota->load(['leaveType', 'creator']), 'Leave quota created', 201);
    }

    public function show($employeeId, EmployeeLeaveQuota $leaveQuota)
    {
        return $this->success($leaveQuota->load(['leaveType', 'creator', 'histories.creator']));
    }

    public function update(UpdateEmployeeLeaveQuotaRequest $request, $employeeId, EmployeeLeaveQuota $leaveQuota)
    {
        $v = $request->validated();
        $leaveQuota = $this->leaveQuotaService->update($leaveQuota, $v);
        return $this->success($leaveQuota->load(['leaveType', 'creator']), 'Updated successfully');
    }

    public function destroy($employeeId, EmployeeLeaveQuota $leaveQuota)
    {
        $this->leaveQuotaService->delete($leaveQuota);
        return $this->success(null, 'Deleted successfully');
    }

    // Adjust leave quota
    public function adjust(AdjustLeaveQuotaRequest $request, $employeeId, EmployeeLeaveQuota $leaveQuota)
    {
        $v = $request->validated();
        $leaveQuota = $this->leaveQuotaService->adjust($leaveQuota, $v, $request->user()->id);

        return $this->success($leaveQuota->load(['leaveType', 'creator', 'histories.creator']), 'Leave quota adjusted');
    }
}
