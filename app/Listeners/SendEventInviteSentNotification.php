<?php

namespace App\Listeners;

use App\Events\EventInviteSent;
use App\Notifications\EventInviteSentNotification;
use App\Models\User;

class SendEventInviteSentNotification
{
    public function handle(EventInviteSent $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new EventInviteSentNotification($event));}
    }
}
