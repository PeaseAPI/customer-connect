<?php

namespace App\Listeners;

use App\Events\SubTaskCompleted;
use App\Notifications\SubTaskCompletedNotification;
use App\Models\User;

class SendSubTaskCompletedNotification
{
    public function handle(SubTaskCompleted $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new SubTaskCompletedNotification($event));}
    }
}
