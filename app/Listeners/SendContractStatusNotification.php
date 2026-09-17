<?php

namespace App\Listeners;

use App\Events\ContractStatusChanged;
use App\Notifications\ContractStatusNotification;
use App\Models\User;

class SendContractStatusNotification
{
    public function handle(ContractStatusChanged $event): void
    {
        $contract = $event->contract;
        if ($contract->owner_id) {
            $owner = User::find($contract->owner_id);
            if ($owner) {
                $owner->notify(new ContractStatusNotification($contract, $event->newStatus));
            }
        }
    }
}
