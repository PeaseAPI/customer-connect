<?php

namespace App\Notifications;

use App\Events\DiscussionReplyAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscussionReplyAddedNotification extends Notification
{
    use Queueable;
    public function __construct(public DiscussionReplyAdded $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'discussion_reply','message'=>'New discussion reply'];}
}
