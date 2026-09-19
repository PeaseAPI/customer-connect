<?php

namespace App\Listeners;

use App\Events\DiscussionCreated;
use App\Notifications\DiscussionCreatedNotification;
use App\Models\User;

class SendDiscussionCreatedNotification
{
    public function handle(DiscussionCreated $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new DiscussionCreatedNotification($event));}
    }
}
