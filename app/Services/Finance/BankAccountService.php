<?php

namespace App\Services\Finance;

use App\Models\BankAccount;

class BankAccountService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = BankAccount::with('currency');

        if (!empty($filters['search'])) {
            $query->where('bank_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): BankAccount
    {
        return BankAccount::create($data);
    }

    public function update(BankAccount $bankAccount, array $data): BankAccount
    {
        $bankAccount->update($data);
        return $bankAccount->fresh();
    }

    public function delete(BankAccount $bankAccount): bool
    {
        return $bankAccount->delete();
    }
}
