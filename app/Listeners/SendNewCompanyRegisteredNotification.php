<?php

namespace App\Listeners;

use App\Events\NewCompanyRegistered;
use App\Notifications\NewCompanyRegisteredNotification;
use App\Models\User;

class SendNewCompanyRegisteredNotification
{
    public function handle(NewCompanyRegistered $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewCompanyRegisteredNotification($event));}
    }
}
