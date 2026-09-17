<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function list(int $userCompanyId, bool $isSuperAdmin, array $filters = [], int $perPage = 15)
    {
        $query = Company::query();

        if (!$isSuperAdmin) {
            $query->where('id', $userCompanyId);
        }

        if (!empty($filters['search'])) {
            $query->where('company_name', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);
        return $company->fresh();
    }
}
