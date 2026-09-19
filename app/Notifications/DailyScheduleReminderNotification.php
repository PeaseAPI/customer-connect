<?php

namespace App\Notifications;

use App\Events\DailyScheduleReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyScheduleReminderNotification extends Notification
{
    use Queueable;
    public function __construct(public DailyScheduleReminder $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'daily_schedule','message'=>'Events scheduled today'];}
}
