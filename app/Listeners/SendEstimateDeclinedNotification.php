<?php

namespace App\Listeners;

use App\Events\EstimateDeclined;
use App\Notifications\EstimateDeclinedNotification;
use App\Models\User;

class SendEstimateDeclinedNotification
{
    public function handle(EstimateDeclined $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new EstimateDeclinedNotification($event));}
    }
}
