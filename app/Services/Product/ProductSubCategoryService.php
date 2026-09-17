<?php

namespace App\Services\Product;

use App\Models\ProductSubCategory;

class ProductSubCategoryService
{
    public function list(int $categoryId, array $filters = [], int $perPage = 15)
    {
        $query = ProductSubCategory::where('category_id', $categoryId);

        if (!empty($filters['search'])) {
            $query->where('sub_category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ProductSubCategory
    {
        return ProductSubCategory::create($data);
    }

    public function update(ProductSubCategory $subCategory, array $data): ProductSubCategory
    {
        $subCategory->update($data);
        return $subCategory->fresh();
    }

    public function delete(ProductSubCategory $subCategory): bool
    {
        return $subCategory->delete();
    }
}
