<?php

namespace App\Listeners;

use App\Events\InvoiceReminderAfter;
use App\Notifications\InvoiceReminderAfterNotification;
use App\Models\User;

class SendInvoiceReminderAfterNotification
{
    public function handle(InvoiceReminderAfter $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new InvoiceReminderAfterNotification($event));}
    }
}
