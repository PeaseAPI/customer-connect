<?php

namespace App\Notifications;

use App\Events\TaskReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification
{
    use Queueable;
    public function __construct(public TaskReminder $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'task_reminder','message'=>'Task deadline approaching'];}
}
