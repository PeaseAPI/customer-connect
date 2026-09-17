<?php

namespace App\Services\Finance;

use App\Models\ExpenseRecurring;

class ExpenseRecurringService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ExpenseRecurring::with(['expense']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ExpenseRecurring
    {
        return ExpenseRecurring::create($data);
    }

    public function update(ExpenseRecurring $expenseRecurring, array $data): ExpenseRecurring
    {
        $expenseRecurring->update($data);
        return $expenseRecurring->fresh();
    }

    public function delete(ExpenseRecurring $expenseRecurring): bool
    {
        return $expenseRecurring->delete();
    }
}
