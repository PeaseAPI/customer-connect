<?php

namespace App\Services\Finance;

use App\Enums\ExpenseStatus;
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
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('item_name', 'like', "%{$filters['search']}%")
                  ->orWhere('purchase_from', 'like', "%{$filters['search']}%");
            });
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
        $status = $data['status'] ?? null;
        unset($data['status']);

        $expense->update($data);

        // 状态变更复用审批副作用(approved_by/approved_at/approval_remark)
        if ($status !== null) {
            match ($status) {
                ExpenseStatus::Approved->value => $this->approve($expense, (int) (auth()->id() ?? 0), $data['approval_remark'] ?? null),
                ExpenseStatus::Declined->value => $this->reject($expense, (int) (auth()->id() ?? 0), $data['approval_remark'] ?? ($data['note'] ?? 'Rejected via update')),
                default => $expense->update(['status' => ExpenseStatus::from($status)]),
            };
        }

        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

                public function approve(Expense $expense, int $approvedBy, ?string $remark = null): Expense
    {
        $expense->update([
            'status' => ExpenseStatus::Approved,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_remark' => $remark,
        ]);
        return $expense->fresh();
    }

    public function reject(Expense $expense, int $rejectedBy, string $reason): Expense
    {
        $expense->update([
            'status' => ExpenseStatus::Declined,
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'approval_remark' => $reason,
        ]);
        return $expense->fresh();
    }
}
