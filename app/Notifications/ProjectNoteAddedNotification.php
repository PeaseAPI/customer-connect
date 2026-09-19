<?php

namespace App\Notifications;

use App\Events\ProjectNoteAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectNoteAddedNotification extends Notification
{
    use Queueable;
    public function __construct(public ProjectNoteAdded $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'project_note','message'=>'New project note'];}
}
