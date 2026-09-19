<?php

namespace App\Notifications;

use App\Events\BirthdayReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BirthdayReminderNotification extends Notification
{
    use Queueable;
    public function __construct(public BirthdayReminder $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'birthday_reminder','message'=>'Birthday reminder'];}
}
