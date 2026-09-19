<?php

namespace App\Listeners;

use App\Events\NewUserRegistered;
use App\Notifications\NewUserRegisteredNotification;
use App\Models\User;

class SendNewUserRegisteredNotification
{
    public function handle(NewUserRegistered $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewUserRegisteredNotification($event));}
    }
}
