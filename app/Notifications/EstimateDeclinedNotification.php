<?php

namespace App\Notifications;

use App\Events\EstimateDeclined;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstimateDeclinedNotification extends Notification
{
    use Queueable;
    public function __construct(public EstimateDeclined $event){}
    public function via(object $n): array{
        return ['database']; // database通道可用(表已重构为混合结构), mail待自定义channel
    }

    public function toDatabase(object $n): array{return ['type'=>'estimate_declined','message'=>'Estimate declined'];}
}
