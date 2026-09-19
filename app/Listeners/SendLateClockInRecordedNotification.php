<?php

namespace App\Listeners;

use App\Events\LateClockInRecorded;
use App\Notifications\LateClockInRecordedNotification;
use App\Models\User;

class SendLateClockInRecordedNotification
{
    public function handle(LateClockInRecorded $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new LateClockInRecordedNotification($event));}
    }
}
