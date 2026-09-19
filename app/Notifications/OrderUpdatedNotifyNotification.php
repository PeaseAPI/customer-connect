<?php

namespace App\Notifications;

use App\Events\OrderUpdatedNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderUpdatedNotifyNotification extends Notification
{
    use Queueable;
    public function __construct(public OrderUpdatedNotify $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'order_updated','message'=>'Order updated'];}
}
