<?php

namespace App\Services\Finance;

use App\Models\ExpenseCategory;

class ExpenseCategoryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ExpenseCategory::query();

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function update(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        $expenseCategory->update($data);
        return $expenseCategory->fresh();
    }

    public function delete(ExpenseCategory $expenseCategory): bool
    {
        return $expenseCategory->delete();
    }
}
