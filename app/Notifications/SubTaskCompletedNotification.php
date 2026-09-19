<?php

namespace App\Notifications;

use App\Events\SubTaskCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubTaskCompletedNotification extends Notification
{
    use Queueable;
    public function __construct(public SubTaskCompleted $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'subtask_completed','message'=>'Subtask completed'];}
}
