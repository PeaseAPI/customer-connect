<?php

namespace App\Services\HRM;

use App\Models\Holiday;

class HolidayService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Holiday::query();

        if (!empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }
        if (!empty($filters['month'])) {
            $query->whereMonth('date', $filters['month']);
        }
        if (!empty($filters['search'])) {
            $query->where('holiday_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest('date')->paginate($perPage);
    }

    public function create(array $data): Holiday
    {
        return Holiday::create($data);
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);
        return $holiday->fresh();
    }

    public function delete(Holiday $holiday): bool
    {
        return $holiday->delete();
    }
}
