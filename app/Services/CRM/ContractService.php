<?php

namespace App\Services\CRM;

use App\Models\Contract;
use App\Enums\ContractStatus;
use App\Events\ContractCreated;
use App\Events\ContractStatusChanged;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Contract::with(['client', 'project', 'creator', 'contractType', 'currency']);

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        if (!empty($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }
        if (!empty($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Contract
    {
        return DB::transaction(function () use ($data) {
            $data['hash'] = $data['hash'] ?? Str::random(32);
            $contract = Contract::create($data);
            event(new ContractCreated($contract));
            return $contract;
        });
    }

    public function update(Contract $contract, array $data): Contract
    {
        // Status changes must go through changeStatus() to dispatch events
        unset($data['status']);

        $contract->update($data);
        return $contract->fresh();
    }

    public function delete(Contract $contract): bool
    {
        return $contract->delete();
    }

    public function changeStatus(Contract $contract, string $newStatus, ?string $reason = null): Contract
    {
        $oldStatus = $contract->status?->value ?? (string) $contract->getRawOriginal('status');
        $contract->update(['status' => $newStatus]);
        event(new ContractStatusChanged($contract, $oldStatus, $newStatus, $reason));
        return $contract->fresh();
    }

    public function renew(Contract $contract, array $data, int $userId): Contract
    {
        return DB::transaction(function () use ($contract, $data, $userId) {
            $contract->update(['status' => ContractStatus::Expired]);

            $newContract = Contract::create([
                'client_id' => $contract->client_id,
                'subject' => $contract->subject,
                'contract_type_id' => $contract->contract_type_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'value' => $data['amount'] ?? $contract->value,
                'currency_id' => $contract->currency_id,
                'description' => $contract->description,
                'company_id' => $contract->company_id,
                'created_by' => $userId,
                'original_contract_number' => $contract->contract_number,
                'hash' => Str::random(32),
            ]);

            $contract->renewHistory()->create([
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'amount' => $data['amount'] ?? null,
                'note' => $data['note'] ?? null,
                'added_by' => $userId,
                'company_id' => $contract->company_id,
            ]);

            return $newContract;
        });
    }

    public function getExpiringSoon(int $days = 30)
    {
                return Contract::where('status', ContractStatus::Active)
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now())
            ->with(['client', 'creator'])
            ->get();
    }

        public function getTotalAmountByPeriod(?string $startDate = null, ?string $endDate = null): float
    {
        $query = Contract::where('status', ContractStatus::Active);
        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
        }
        return $query->sum('value');
    }
}
