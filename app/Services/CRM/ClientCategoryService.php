<?php

namespace App\Services\CRM;

use App\Models\ClientCategory;

class ClientCategoryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ClientCategory::with(['subCategories']);

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ClientCategory
    {
        return ClientCategory::create($data);
    }

    public function update(ClientCategory $clientCategory, array $data): ClientCategory
    {
        $clientCategory->update($data);
        return $clientCategory->fresh();
    }

    public function delete(ClientCategory $clientCategory): bool
    {
        $clientCategory->subCategories()->delete();
        return $clientCategory->delete();
    }
}
