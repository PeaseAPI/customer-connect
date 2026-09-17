<?php

namespace App\Services\Product;

use App\Models\UnitType;

class UnitTypeService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = UnitType::query();

        if (!empty($filters['search'])) {
            $query->where('unit_type', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): UnitType
    {
        return UnitType::create($data);
    }

    public function update(UnitType $unitType, array $data): UnitType
    {
        $unitType->update($data);
        return $unitType->fresh();
    }

    public function delete(UnitType $unitType): bool
    {
        return $unitType->delete();
    }
}
