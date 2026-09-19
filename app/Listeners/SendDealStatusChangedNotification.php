<?php

namespace App\Listeners;

use App\Events\DealStatusChanged;
use App\Notifications\DealStatusChangedNotification;
use App\Models\User;

class SendDealStatusChangedNotification
{
    public function handle(DealStatusChanged $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new DealStatusChangedNotification($event));}
    }
}
