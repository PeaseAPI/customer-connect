<?php

namespace App\Services\Finance;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Expense::with(['user', 'project', 'category', 'currency']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('purchase_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('purchase_date', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        // Status changes must go through approve() or reject()
        unset($data['status']);

        $expense->update($data);
        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    public function approve(Expense $expense): Expense
    {
        $expense->update(['status' => 'approved']);
        return $expense->fresh();
    }

    public function reject(Expense $expense): Expense
    {
        $expense->update(['status' => 'declined']);
        return $expense->fresh();
    }
}
