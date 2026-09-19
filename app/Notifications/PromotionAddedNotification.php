<?php

namespace App\Notifications;

use App\Events\PromotionAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PromotionAddedNotification extends Notification
{
    use Queueable;
    public function __construct(public PromotionAdded $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'promotion','message'=>'You have been promoted'];}
}
