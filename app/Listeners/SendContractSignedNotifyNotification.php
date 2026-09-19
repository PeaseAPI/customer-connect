<?php

namespace App\Listeners;

use App\Events\ContractSignedNotify;
use App\Notifications\ContractSignedNotifyNotification;
use App\Models\User;

class SendContractSignedNotifyNotification
{
    public function handle(ContractSignedNotify $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new ContractSignedNotifyNotification($event));}
    }
}
