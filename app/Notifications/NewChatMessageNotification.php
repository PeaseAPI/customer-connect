<?php

namespace App\Notifications;

use App\Events\NewChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewChatMessageNotification extends Notification
{
    use Queueable;
    public function __construct(public NewChatMessage $event){}
    public function via(object $n): array{
        return ['database']; // database通道可用(表已重构为混合结构), mail待自定义channel
    }

    public function toDatabase(object $n): array{return ['type'=>'new_chat','message'=>'New chat message'];}
}
