<?php

namespace App\Notifications;

use App\Events\ProjectReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectReminderNotification extends Notification
{
    use Queueable;
    public function __construct(public ProjectReminder $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'project_reminder','message'=>'Project deadline approaching'];}
}
