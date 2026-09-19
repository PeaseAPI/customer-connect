<?php

namespace App\Listeners;

use App\Events\InvoiceUpdated;
use App\Notifications\InvoiceUpdatedNotification;
use App\Models\User;

class SendInvoiceUpdatedNotification
{
    public function handle(InvoiceUpdated $event): void
    {
        $cu=User::where('company_id',app('App\Services\ContextService')->getCompanyId())->whereHas('roles',fn($q)=>$q->where('name','admin'))->get();
        foreach($cu as $u){$u->notify(new InvoiceUpdatedNotification($event));}
    }
}
