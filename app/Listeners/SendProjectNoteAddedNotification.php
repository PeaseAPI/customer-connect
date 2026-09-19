<?php

namespace App\Listeners;

use App\Events\ProjectNoteAdded;
use App\Notifications\ProjectNoteAddedNotification;
use App\Models\User;

class SendProjectNoteAddedNotification
{
    public function handle(ProjectNoteAdded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new ProjectNoteAddedNotification($event));}
    }
}
