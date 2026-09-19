<?php

namespace App\Notifications;

use App\Events\AppreciationGiven;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppreciationGivenNotification extends Notification
{
    use Queueable;
    public function __construct(public AppreciationGiven $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'appreciation','message'=>'You received appreciation'];}
}
