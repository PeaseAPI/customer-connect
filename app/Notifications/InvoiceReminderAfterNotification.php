<?php

namespace App\Notifications;

use App\Events\InvoiceReminderAfter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceReminderAfterNotification extends Notification
{
    use Queueable;
    public function __construct(public InvoiceReminderAfter $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'invoice_overdue','message'=>'Invoice payment overdue'];}
}
