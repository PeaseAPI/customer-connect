<?php

namespace App\Listeners;

use App\Events\PromotionAdded;
use App\Notifications\PromotionAddedNotification;
use App\Models\User;

class SendPromotionAddedNotification
{
    public function handle(PromotionAdded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new PromotionAddedNotification($event));}
    }
}
