<?php

namespace App\Listeners;

use App\Events\NewOrderPlaced;
use App\Notifications\NewOrderPlacedNotification;
use App\Models\User;

class SendNewOrderPlacedNotification
{
    public function handle(NewOrderPlaced $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewOrderPlacedNotification($event));}
    }
}
