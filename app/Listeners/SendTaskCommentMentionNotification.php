<?php

namespace App\Listeners;

use App\Events\TaskCommentMention;
use App\Notifications\TaskCommentMentionNotification;
use App\Models\User;

class SendTaskCommentMentionNotification
{
    public function handle(TaskCommentMention $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TaskCommentMentionNotification($event));}
    }
}
