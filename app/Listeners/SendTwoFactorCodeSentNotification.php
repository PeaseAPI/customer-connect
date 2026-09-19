<?php

namespace App\Listeners;

use App\Events\TwoFactorCodeSent;
use App\Notifications\TwoFactorCodeSentNotification;
use App\Models\User;

class SendTwoFactorCodeSentNotification
{
    public function handle(TwoFactorCodeSent $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new TwoFactorCodeSentNotification($event));}
    }
}
