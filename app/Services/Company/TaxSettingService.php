<?php

namespace App\Services\Company;

use App\Models\TaxSetting;

class TaxSettingService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = TaxSetting::query();

        if (!empty($filters['is_active'])) {
            $query->where('is_active', true);
        }
        if (!empty($filters['search'])) {
            $query->where('tax_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): TaxSetting
    {
        return TaxSetting::create($data);
    }

    public function update(TaxSetting $taxSetting, array $data): TaxSetting
    {
        $taxSetting->update($data);
        return $taxSetting->fresh();
    }

    public function delete(TaxSetting $taxSetting): bool
    {
        return $taxSetting->delete();
    }
}

