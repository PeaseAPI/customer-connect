<?php

namespace App\Notifications;

use App\Events\TaskCommentAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommentAddedNotification extends Notification
{
    use Queueable;
    public function __construct(public TaskCommentAdded $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'task_comment','message'=>'New comment on task'];}
}
