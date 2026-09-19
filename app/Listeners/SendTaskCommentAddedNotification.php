<?php

namespace App\Listeners;

use App\Events\TaskCommentAdded;
use App\Notifications\TaskCommentAddedNotification;
use App\Models\User;

class SendTaskCommentAddedNotification
{
    public function handle(TaskCommentAdded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TaskCommentAddedNotification($event));}
    }
}
