<?php

namespace App\Notifications;

use App\Events\EventReminderSent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventReminderSentNotification extends Notification
{
    use Queueable;
    public function __construct(public EventReminderSent $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'event_reminder','message'=>'Event starting soon'];}
}
