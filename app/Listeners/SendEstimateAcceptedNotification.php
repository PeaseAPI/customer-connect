<?php

namespace App\Listeners;

use App\Events\EstimateAccepted;
use App\Notifications\EstimateAcceptedNotification;
use App\Models\User;

class SendEstimateAcceptedNotification
{
    public function handle(EstimateAccepted $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new EstimateAcceptedNotification($event));}
    }
}
