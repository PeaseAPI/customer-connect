<?php

namespace App\Services\HRM;

use App\Models\Shift;

class ShiftService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Shift::query();

        if (!empty($filters['search'])) {
            $query->where('shift_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Shift
    {
        return Shift::create($data);
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift->fresh();
    }

    public function delete(Shift $shift): bool
    {
        return $shift->delete();
    }
}
