<?php

namespace App\Observers;

use App\Models\Contract;
use App\Notifications\ContractExpiringNotification;
use App\Jobs\CheckExpiringContracts;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        // 新建合同时记录活动日志
        activity()
            ->performedOn($contract)
            ->log('创建合同');
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
                ->log('合同状态变更');
        }
    }

    public function deleted(Contract $contract): void
    {
        activity()
            ->performedOn($contract)
            ->log('删除合同');
    }
}
