<?php

namespace App\Notifications;

use App\Events\NewUserRegistered;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification
{
    use Queueable;
    public function __construct(public NewUserRegistered $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_user','message'=>'New user registered'];}
}
