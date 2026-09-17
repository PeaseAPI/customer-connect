<?php

namespace App\Services\Company;

use App\Models\CustomField;

class CustomFieldService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = CustomField::query();

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        if (!empty($filters['is_active'])) {
            $query->where('is_active', true);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): CustomField
    {
        return CustomField::create($data);
    }

    public function update(CustomField $customField, array $data): CustomField
    {
        $customField->update($data);
        return $customField->fresh();
    }

    public function delete(CustomField $customField): bool
    {
        $customField->values()->delete();
        return $customField->delete();
    }
}
