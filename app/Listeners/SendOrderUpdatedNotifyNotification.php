<?php

namespace App\Listeners;

use App\Events\OrderUpdatedNotify;
use App\Notifications\OrderUpdatedNotifyNotification;
use App\Models\User;

class SendOrderUpdatedNotifyNotification
{
    public function handle(OrderUpdatedNotify $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new OrderUpdatedNotifyNotification($event));}
    }
}
