<?php

namespace App\Services\Product;

use App\Models\ProductCategory;

class ProductCategoryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ProductCategory::with(['subCategories']);

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }

    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $productCategory->update($data);
        return $productCategory->fresh();
    }

    public function delete(ProductCategory $productCategory): bool
    {
        $productCategory->subCategories()->delete();
        return $productCategory->delete();
    }
}

