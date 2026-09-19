<?php

namespace App\Listeners;

use App\Events\InvoiceReminder;
use App\Notifications\InvoiceReminderNotification;
use App\Models\User;

class SendInvoiceReminderNotification
{
    public function handle(InvoiceReminder $event): void
    {
        $companyUsers = User::where('company_id', $event->invoice->company_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
        foreach ($companyUsers as $user) {
            $user->notify(new InvoiceReminderNotification($event));
        }
    }
}
