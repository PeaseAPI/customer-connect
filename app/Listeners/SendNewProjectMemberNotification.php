<?php

namespace App\Listeners;

use App\Events\NewProjectMember;
use App\Notifications\NewProjectMemberNotification;
use App\Models\User;

class SendNewProjectMemberNotification
{
    public function handle(NewProjectMember $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewProjectMemberNotification($event));}
    }
}
