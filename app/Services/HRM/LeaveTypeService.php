<?php

namespace App\Services\HRM;

use App\Models\LeaveType;

class LeaveTypeService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = LeaveType::query();

        if (!empty($filters['search'])) {
            $query->where('type_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create($data);
    }

    public function update(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);
        return $leaveType->fresh();
    }

    public function delete(LeaveType $leaveType): bool
    {
        return $leaveType->delete();
    }
}
