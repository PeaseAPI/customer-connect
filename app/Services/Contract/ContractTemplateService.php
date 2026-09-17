<?php

namespace App\Services\Contract;

use App\Models\ContractTemplate;

class ContractTemplateService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ContractTemplate::with(['contractType', 'creator']);

        if (!empty($filters['contract_type_id'])) {
            $query->where('contract_type_id', $filters['contract_type_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('subject', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ContractTemplate
    {
        return ContractTemplate::create($data);
    }

    public function update(ContractTemplate $contractTemplate, array $data): ContractTemplate
    {
        $contractTemplate->update($data);
        return $contractTemplate->fresh();
    }

    public function delete(ContractTemplate $contractTemplate): bool
    {
        return $contractTemplate->delete();
    }
}
