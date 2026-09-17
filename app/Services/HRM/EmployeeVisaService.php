<?php

namespace App\Services\HRM;

use App\Models\EmployeeVisa;

class EmployeeVisaService
{
    public function list(int $employeeId, array $filters = [], int $perPage = 15)
    {
        $query = EmployeeVisa::where('user_id', $employeeId)->with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('visa_type', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['issuing_country'])) {
            $query->where('issuing_country', $filters['issuing_country']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): EmployeeVisa
    {
        return EmployeeVisa::create($data);
    }

    public function update(EmployeeVisa $visa, array $data): EmployeeVisa
    {
        $visa->update($data);
        return $visa->fresh();
    }

    public function delete(EmployeeVisa $visa): bool
    {
        return $visa->delete();
    }
}
