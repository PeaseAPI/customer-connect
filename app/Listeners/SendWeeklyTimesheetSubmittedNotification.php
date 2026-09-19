<?php

namespace App\Listeners;

use App\Events\WeeklyTimesheetSubmitted;
use App\Notifications\WeeklyTimesheetSubmittedNotification;
use App\Models\User;

class SendWeeklyTimesheetSubmittedNotification
{
    public function handle(WeeklyTimesheetSubmitted $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new WeeklyTimesheetSubmittedNotification($event));}
    }
}
