<?php

namespace App\Listeners;

use App\Events\NewChatMessage;
use App\Notifications\NewChatMessageNotification;
use App\Models\User;

class SendNewChatMessageNotification
{
    public function handle(NewChatMessage $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewChatMessageNotification($event));}
    }
}
