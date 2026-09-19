<?php

namespace App\Listeners;

use App\Events\ContractExpiringSoon;
use App\Notifications\ContractExpiringSoonNotification;
use App\Models\User;

class SendContractExpiringSoonNotification
{
    public function handle(ContractExpiringSoon $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new ContractExpiringSoonNotification($event));}
    }
}
