<?php

namespace App\Services\Finance;

use App\Models\Tax;

class TaxService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Tax::query();

        if (!empty($filters['is_active'])) {
            $query->where('is_active', true);
        }
        if (!empty($filters['search'])) {
            $query->where('tax_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Tax
    {
        return Tax::create($data);
    }

    public function update(Tax $tax, array $data): Tax
    {
        $tax->update($data);
        return $tax->fresh();
    }

    public function delete(Tax $tax): bool
    {
        return $tax->delete();
    }
}
