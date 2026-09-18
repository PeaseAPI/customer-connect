<?php

namespace App\Services\HRM;

use App\Models\EmployeeLeaveQuota;
use App\Models\EmployeeLeaveQuotaHistory;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveQuotaService
{
    public function list(int $employeeId, int $perPage = 15)
    {
        return EmployeeLeaveQuota::where('user_id', $employeeId)
            ->with(['leaveType', 'creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(int $employeeId, array $data, int $userId): EmployeeLeaveQuota
    {
        return DB::transaction(function () use ($employeeId, $data, $userId) {
            $data['user_id'] = $employeeId;
            $data['leaves_used'] = 0;
            $data['leaves_remaining'] = $data['no_of_leaves'];
            $data['added_by'] = $userId;

            $quota = EmployeeLeaveQuota::create($data);

            EmployeeLeaveQuotaHistory::create([
                'company_id' => $quota->company_id,
                'leave_quota_id' => $quota->id,
                'action' => 'added',
                'amount' => $data['no_of_leaves'],
                'reason' => 'Initial assignment',
                'added_by' => $userId,
            ]);

            return $quota;
        });
    }

    public function update(EmployeeLeaveQuota $leaveQuota, array $data): EmployeeLeaveQuota
    {
        if (isset($data['no_of_leaves'])) {
            $data['leaves_remaining'] = $data['no_of_leaves'] - ($data['leaves_used'] ?? $leaveQuota->leaves_used);
        }
        $leaveQuota->update($data);
        return $leaveQuota->fresh();
    }

    public function delete(EmployeeLeaveQuota $leaveQuota): bool
    {
        return $leaveQuota->delete();
    }

    public function adjust(EmployeeLeaveQuota $leaveQuota, array $data, int $userId): EmployeeLeaveQuota
    {
        return DB::transaction(function () use ($leaveQuota, $data, $userId) {
            if ($data['action'] === 'added') {
                $leaveQuota->increment('no_of_leaves', $data['amount']);
                $leaveQuota->increment('leaves_remaining', $data['amount']);
            } elseif ($data['action'] === 'deducted') {
                $leaveQuota->decrement('leaves_remaining', $data['amount']);
            } elseif ($data['action'] === 'reset') {
                $leaveQuota->update([
                    'no_of_leaves' => $data['amount'],
                    'leaves_used' => 0,
                    'leaves_remaining' => $data['amount'],
                ]);
            }

            EmployeeLeaveQuotaHistory::create([
                'company_id' => $leaveQuota->company_id,
                'leave_quota_id' => $leaveQuota->id,
                'action' => $data['action'],
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
                'added_by' => $userId,
            ]);

            return $leaveQuota->fresh();
        });
    }
}
