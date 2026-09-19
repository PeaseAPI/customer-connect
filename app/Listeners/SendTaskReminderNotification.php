<?php

namespace App\Listeners;

use App\Events\TaskReminder;
use App\Notifications\TaskReminderNotification;
use App\Models\User;

class SendTaskReminderNotification
{
    public function handle(TaskReminder $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TaskReminderNotification($event));}
    }
}
