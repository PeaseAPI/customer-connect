<?php

namespace App\Notifications;

use App\Events\ChatMention;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatMentionNotification extends Notification
{
    use Queueable;
    public function __construct(public ChatMention $event){}
    public function via(object $n): array{
        return []; // TODO: notifications表为自定义结构(company_id/title/message), 待实现自定义channel后再启用database/mail通道
    }

    public function toDatabase(object $n): array{return ['type'=>'chat_mention','message'=>'Mentioned in chat'];}
}
