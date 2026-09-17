<?php

namespace App\Services\HRM;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Department::with(['company']);

        if (!empty($filters['search'])) {
            $query->where('department_name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department->fresh();
    }

    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
