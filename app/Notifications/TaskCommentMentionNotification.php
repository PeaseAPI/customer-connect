<?php

namespace App\Notifications;

use App\Events\TaskCommentMention;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommentMentionNotification extends Notification
{
    use Queueable;
    public function __construct(public TaskCommentMention $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'task_comment_mention','message'=>'Mentioned in task comment'];}
}
