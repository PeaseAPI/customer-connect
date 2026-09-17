<?php

namespace App\Services\Contract;

use App\Models\ContractType;

class ContractTypeService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ContractType::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ContractType
    {
        return ContractType::create($data);
    }

    public function update(ContractType $contractType, array $data): ContractType
    {
        $contractType->update($data);
        return $contractType->fresh();
    }

    public function delete(ContractType $contractType): bool
    {
        return $contractType->delete();
    }
}
