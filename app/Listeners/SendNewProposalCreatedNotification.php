<?php

namespace App\Listeners;

use App\Events\NewProposalCreated;
use App\Notifications\NewProposalCreatedNotification;
use App\Models\User;

class SendNewProposalCreatedNotification
{
    public function handle(NewProposalCreated $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewProposalCreatedNotification($event));}
    }
}
