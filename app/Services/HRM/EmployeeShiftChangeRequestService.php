<?php

namespace App\Services\HRM;

use App\Models\EmployeeShiftChangeRequest;

class EmployeeShiftChangeRequestService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = EmployeeShiftChangeRequest::with(['user', 'shift', 'currentShift', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['search'])) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$filters['search']}%"));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): EmployeeShiftChangeRequest
    {
        return EmployeeShiftChangeRequest::create($data);
    }

    public function approve(EmployeeShiftChangeRequest $shiftChangeRequest, int $approvedBy): EmployeeShiftChangeRequest
    {
        $shiftChangeRequest->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
        return $shiftChangeRequest->fresh();
    }

    public function reject(EmployeeShiftChangeRequest $shiftChangeRequest, int $approvedBy): EmployeeShiftChangeRequest
    {
        $shiftChangeRequest->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
        return $shiftChangeRequest->fresh();
    }

    public function delete(EmployeeShiftChangeRequest $shiftChangeRequest): bool
    {
        return $shiftChangeRequest->delete();
    }
}
