<?php

namespace App\Listeners;

use App\Events\NewNotice;
use App\Notifications\NewNoticeNotification;
use App\Models\User;

class SendNewNoticeNotification
{
    public function handle(NewNotice $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewNoticeNotification($event));}
    }
}
