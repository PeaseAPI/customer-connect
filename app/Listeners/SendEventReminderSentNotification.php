<?php

namespace App\Listeners;

use App\Events\EventReminderSent;
use App\Notifications\EventReminderSentNotification;
use App\Models\User;

class SendEventReminderSentNotification
{
    public function handle(EventReminderSent $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new EventReminderSentNotification($event));}
    }
}
