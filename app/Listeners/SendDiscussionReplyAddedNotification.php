<?php

namespace App\Listeners;

use App\Events\DiscussionReplyAdded;
use App\Notifications\DiscussionReplyAddedNotification;
use App\Models\User;

class SendDiscussionReplyAddedNotification
{
    public function handle(DiscussionReplyAdded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new DiscussionReplyAddedNotification($event));}
    }
}
