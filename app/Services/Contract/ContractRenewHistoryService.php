<?php

namespace App\Services\Contract;

use App\Models\ContractRenewHistory;

class ContractRenewHistoryService
{
    public function list(int $contractId, int $perPage = 15)
    {
        return ContractRenewHistory::where('contract_id', $contractId)
            ->with(['creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ContractRenewHistory
    {
        return ContractRenewHistory::create($data);
    }

    public function update(ContractRenewHistory $renewHistory, array $data): ContractRenewHistory
    {
        $renewHistory->update($data);
        return $renewHistory->fresh();
    }

    public function delete(ContractRenewHistory $renewHistory): bool
    {
        return $renewHistory->delete();
    }
}

