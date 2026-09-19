<?php

namespace App\Listeners;

use App\Events\AppreciationGiven;
use App\Notifications\AppreciationGivenNotification;
use App\Models\User;

class SendAppreciationGivenNotification
{
    public function handle(AppreciationGiven $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new AppreciationGivenNotification($event));}
    }
}
