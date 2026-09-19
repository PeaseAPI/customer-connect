<?php

namespace App\Notifications;

use App\Events\InvoiceUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceUpdatedNotification extends Notification
{
    use Queueable;
    public function __construct(public InvoiceUpdated $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'invoice_updated','message'=>'Invoice updated'];}
}
