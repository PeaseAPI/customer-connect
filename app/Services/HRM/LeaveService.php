<?php

namespace App\Services\HRM;

use App\Enums\ApprovalStatus;
use App\Models\Leave;
use App\Models\EmployeeLeaveQuota;
use App\Events\LeaveStatusChanged;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Leave::with(['user', 'approver', 'leaveType']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }
        if (!empty($filters['start_date'])) {
            $query->where('leave_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('leave_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Leave
    {
        return DB::transaction(function () use ($data) {
            return Leave::create($data);
        });
    }

    public function approve(Leave $leave, int $approverId, ?string $comment = null): Leave
    {
        return DB::transaction(function () use ($leave, $approverId, $comment) {
            $oldStatus = $leave->status?->value ?? (string) $leave->getRawOriginal('status');
            $leave->update([
                'status' => ApprovalStatus::Approved,
                'approved_by' => $approverId,
            ]);
            $this->deductLeaveBalance($leave);
            event(new LeaveStatusChanged($leave, $oldStatus, 'approved'));
            return $leave->fresh();
        });
    }

    public function reject(Leave $leave, int $approverId, string $reason = ''): Leave
    {
        $oldStatus = $leave->status?->value ?? (string) $leave->getRawOriginal('status');
        $leave->update([
            'status' => ApprovalStatus::Rejected,
            'approved_by' => $approverId,
        ]);
        event(new LeaveStatusChanged($leave, $oldStatus, 'rejected'));
        return $leave->fresh();
    }

    public function cancel(Leave $leave): Leave
    {
        $oldStatus = $leave->status?->value ?? (string) $leave->getRawOriginal('status');
                $leave->update(['status' => ApprovalStatus::Canceled]);
        if ($oldStatus === 'approved') {
            $this->restoreLeaveBalance($leave);
        }
        event(new LeaveStatusChanged($leave, $oldStatus, 'cancelled'));
        return $leave->fresh();
    }

    public function update(Leave $leave, array $data): Leave
    {
        // Status changes must go through approve(), reject(), or cancel()
        // to properly handle leave balance and dispatch events
        unset($data['status'], $data['approved_by']);

        $leave->update($data);
        return $leave->fresh();
    }

    public function delete(Leave $leave): bool
    {
        return $leave->delete();
    }

    public function getLeaveBalance(int $userId, int $leaveTypeId, ?string $year = null): ?EmployeeLeaveQuota
    {
        $year = $year ?? now()->year;
        return EmployeeLeaveQuota::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('cycle', $year)
            ->first();
    }

    private function deductLeaveBalance(Leave $leave): void
    {
        $quota = EmployeeLeaveQuota::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('cycle', now()->year)
            ->first();

        if ($quota) {
            $used = $leave->duration?->value ? (float) $leave->duration->value : 1;
            $quota->increment('leaves_used', $used);
            $quota->decrement('leaves_remaining', $used);
        }
    }

    private function restoreLeaveBalance(Leave $leave): void
    {
        $quota = EmployeeLeaveQuota::where('user_id', $leave->user_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('cycle', now()->year)
            ->first();

        if ($quota) {
            $used = $leave->duration?->value ? (float) $leave->duration->value : 1;
            $quota->decrement('leaves_used', $used);
            $quota->increment('leaves_remaining', $used);
        }
    }
}
