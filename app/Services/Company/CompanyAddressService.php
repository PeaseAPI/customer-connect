<?php

namespace App\Services\Company;

use App\Models\CompanyAddress;

class CompanyAddressService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = CompanyAddress::query();

        if (!empty($filters['is_default'])) {
            $query->where('is_default', true);
        }
        if (!empty($filters['search'])) {
            $query->where('address', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): CompanyAddress
    {
        if (!empty($data['is_default'])) {
            CompanyAddress::where('company_id', $data['company_id'])->update(['is_default' => false]);
        }

        return CompanyAddress::create($data);
    }

    public function update(CompanyAddress $companyAddress, array $data): CompanyAddress
    {
        if (!empty($data['is_default'])) {
            CompanyAddress::where('company_id', $data['company_id'] ?? $companyAddress->company_id)->update(['is_default' => false]);
        }

        $companyAddress->update($data);
        return $companyAddress->fresh();
    }

    public function delete(CompanyAddress $companyAddress): bool
    {
        return $companyAddress->delete();
    }
}
