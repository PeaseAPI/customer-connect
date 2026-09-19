<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;
use App\Notifications\TicketStatusChangedNotification;
use App\Models\User;

class SendTicketStatusChangedNotification
{
    public function handle(TicketStatusChanged $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TicketStatusChangedNotification($event));}
    }
}
