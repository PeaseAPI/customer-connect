<?php

namespace App\Listeners;

use App\Events\BirthdayReminder;
use App\Notifications\BirthdayReminderNotification;
use App\Models\User;

class SendBirthdayReminderNotification
{
    public function handle(BirthdayReminder $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new BirthdayReminderNotification($event));}
    }
}
