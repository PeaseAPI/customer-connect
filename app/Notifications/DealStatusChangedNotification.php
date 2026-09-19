<?php

namespace App\Notifications;

use App\Events\DealStatusChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DealStatusChangedNotification extends Notification
{
    use Queueable;
    public function __construct(public DealStatusChanged $event){}
    public function via(object $n): array{
        return ['database']; // database通道可用(表已重构为混合结构), mail待自定义channel
    }

    public function toDatabase(object $n): array{return ['type'=>'deal_status_changed','message'=>'Deal status changed'];}
}
