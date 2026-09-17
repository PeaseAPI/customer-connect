<?php

namespace App\Services\HRM;

use App\Models\Designation;

class DesignationService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Designation::with(['company']);

        if (!empty($filters['search'])) {
            $query->where('designation_name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Designation
    {
        return Designation::create($data);
    }

    public function update(Designation $designation, array $data): Designation
    {
        $designation->update($data);
        return $designation->fresh();
    }

    public function delete(Designation $designation): bool
    {
        return $designation->delete();
    }
}
