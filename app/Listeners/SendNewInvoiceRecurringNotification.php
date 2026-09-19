<?php

namespace App\Listeners;

use App\Events\NewInvoiceRecurring;
use App\Notifications\NewInvoiceRecurringNotification;
use App\Models\User;

class SendNewInvoiceRecurringNotification
{
    public function handle(NewInvoiceRecurring $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new NewInvoiceRecurringNotification($event));}
    }
}
