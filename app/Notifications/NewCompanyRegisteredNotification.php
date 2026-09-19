<?php

namespace App\Notifications;

use App\Events\NewCompanyRegistered;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCompanyRegisteredNotification extends Notification
{
    use Queueable;
    public function __construct(public NewCompanyRegistered $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_company','message'=>'New company registered'];}
}
