<?php

namespace App\Services\HRM;

use App\Models\Leave;
use App\Models\LeaveBalance;
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
            $leave = Leave::create($data);
            return $leave;
        });
    }

    public function approve(Leave $leave, int $approverId, ?string $comment = null): Leave
    {
        return DB::transaction(function () use ($leave, $approverId, $comment) {
            $oldStatus = $leave->status?->value ?? (string) $leave->getRawOriginal('status');
            $leave->update([
                'status' => 'approved',
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
            'status' => 'rejected',
            'approved_by' => $approverId,
        ]);
        event(new LeaveStatusChanged($leave, $oldStatus, 'rejected'));
        return $leave->fresh();
    }

    public function cancel(Leave $leave): Leave
    {
        $oldStatus = $leave->status?->value ?? (string) $leave->getRawOriginal('status');
        $leave->update(['status' => 'cancelled']);
        if ($oldStatus === 'approved') {
            $this->restoreLeaveBalance($leave);
        }
        event(new LeaveStatusChanged($leave, $oldStatus, 'cancelled'));
        return $leave->fresh();
    }

    public function update(Leave $leave, array $data): Leave
    {
        $leave->update($data);
        return $leave->fresh();
    }

    public function delete(Leave $leave): bool
    {
        return $leave->delete();
    }

    public function getLeaveBalance(int $userId, int $leaveTypeId, ?string $year = null): LeaveBalance
    {
        $year = $year ?? now()->year;
        return LeaveBalance::firstOrCreate([
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'year' => $year,
        ]);
    }

    private function deductLeaveBalance(Leave $leave): void
    {
        try {
            $balance = LeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->increment('used_days', $leave->duration ? (float) $leave->duration : 1);
            }
        } catch (\Throwable $e) {
            // LeaveBalance model/table may not exist yet
            report($e);
        }
    }

    private function restoreLeaveBalance(Leave $leave): void
    {
        try {
            $balance = LeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->decrement('used_days', $leave->duration ? (float) $leave->duration : 1);
            }
        } catch (\Throwable $e) {
            // LeaveBalance model/table may not exist yet
            report($e);
        }
    }
}
