<?php

namespace App\Notifications;

use App\Events\InvoiceReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    use Queueable;
    public function __construct(public InvoiceReminder $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'invoice_reminder','message'=>'Invoice payment reminder'];}
}
