<?php

namespace App\Listeners;

use App\Events\TicketReplyAdded;
use App\Notifications\TicketReplyAddedNotification;
use App\Models\User;

class SendTicketReplyAddedNotification
{
    public function handle(TicketReplyAdded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TicketReplyAddedNotification($event));}
    }
}
