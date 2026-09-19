<?php

namespace App\Notifications;

use App\Events\NewProjectMember;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewProjectMemberNotification extends Notification
{
    use Queueable;
    public function __construct(public NewProjectMember $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_project_member','message'=>'Added to project'];}
}
