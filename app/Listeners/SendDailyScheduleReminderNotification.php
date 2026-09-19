<?php

namespace App\Listeners;

use App\Events\DailyScheduleReminder;
use App\Notifications\DailyScheduleReminderNotification;
use App\Models\User;

class SendDailyScheduleReminderNotification
{
    public function handle(DailyScheduleReminder $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new DailyScheduleReminderNotification($event));}
    }
}
