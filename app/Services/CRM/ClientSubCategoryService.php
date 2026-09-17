<?php

namespace App\Services\CRM;

use App\Models\ClientSubCategory;

class ClientSubCategoryService
{
    public function list(int $categoryId, array $filters = [], int $perPage = 15)
    {
        $query = ClientSubCategory::where('category_id', $categoryId);

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ClientSubCategory
    {
        return ClientSubCategory::create($data);
    }

    public function update(ClientSubCategory $subCategory, array $data): ClientSubCategory
    {
        $subCategory->update($data);
        return $subCategory->fresh();
    }

    public function delete(ClientSubCategory $subCategory): bool
    {
        return $subCategory->delete();
    }
}
