<?php

namespace App\Listeners;

use App\Events\NewExpenseRecurring;
use App\Notifications\NewExpenseRecurringNotification;
use App\Models\User;

class SendNewExpenseRecurringNotification
{
    public function handle(NewExpenseRecurring $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewExpenseRecurringNotification($event));}
    }
}
