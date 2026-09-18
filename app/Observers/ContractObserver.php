<?php

namespace App\Observers;

use App\Models\Contract;
use App\Notifications\ContractExpiringNotification;
use App\Jobs\CheckExpiringContracts;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        // 新建合同时记录Activity log
        activity()
            ->performedOn($contract)
            ->log('Create contract');
    }

    public function updated(Contract $contract): void
    {
        if ($contract->isDirty('status')) {
            activity()
                ->performedOn($contract)
                ->withProperties([
                    'old' => $contract->getOriginal('status'),
                    'new' => $contract->status,
                ])
                ->log('Contract status changed');
        }
    }

    public function deleted(Contract $contract): void
    {
        activity()
            ->performedOn($contract)
            ->log('Delete contract');
    }
}
