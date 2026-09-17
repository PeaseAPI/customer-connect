<?php

namespace App\Services\HRM;

use App\Models\EmployeeShiftSchedule;

class EmployeeShiftScheduleService
{
    public function list(int $employeeId, array $filters = [], int $perPage = 15)
    {
        $query = EmployeeShiftSchedule::where('user_id', $employeeId)->with(['shift']);

        if (!empty($filters['date'])) {
            $query->where('date', $filters['date']);
        }
        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): EmployeeShiftSchedule
    {
        return EmployeeShiftSchedule::create($data);
    }

    public function update(EmployeeShiftSchedule $shiftSchedule, array $data): EmployeeShiftSchedule
    {
        $shiftSchedule->update($data);
        return $shiftSchedule->fresh();
    }

    public function delete(EmployeeShiftSchedule $shiftSchedule): bool
    {
        return $shiftSchedule->delete();
    }
}
