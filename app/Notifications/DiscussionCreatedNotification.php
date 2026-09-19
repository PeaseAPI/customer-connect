<?php

namespace App\Notifications;

use App\Events\DiscussionCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscussionCreatedNotification extends Notification
{
    use Queueable;
    public function __construct(public DiscussionCreated $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'discussion_created','message'=>'New discussion'];}
}
