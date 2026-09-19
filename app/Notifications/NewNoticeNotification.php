<?php

namespace App\Notifications;

use App\Events\NewNotice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewNoticeNotification extends Notification
{
    use Queueable;
    public function __construct(public NewNotice $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_notice','message'=>'New notice posted'];}
}
