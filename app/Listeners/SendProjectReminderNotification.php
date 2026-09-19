<?php

namespace App\Listeners;

use App\Events\ProjectReminder;
use App\Notifications\ProjectReminderNotification;
use App\Models\User;

class SendProjectReminderNotification
{
    public function handle(ProjectReminder $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new ProjectReminderNotification($event));}
    }
}
