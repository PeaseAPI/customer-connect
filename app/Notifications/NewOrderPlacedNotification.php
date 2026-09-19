<?php

namespace App\Notifications;

use App\Events\NewOrderPlaced;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderPlacedNotification extends Notification
{
    use Queueable;
    public function __construct(public NewOrderPlaced $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_order','message'=>'New order placed'];}
}
