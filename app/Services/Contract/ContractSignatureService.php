<?php

namespace App\Services\Contract;

use App\Models\ContractSignature;

class ContractSignatureService
{
    public function list(int $contractId, int $perPage = 15)
    {
        return ContractSignature::where('contract_id', $contractId)
            ->with(['signer'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ContractSignature
    {
        return ContractSignature::create($data);
    }

    public function delete(ContractSignature $signature): bool
    {
        return $signature->delete();
    }
}

