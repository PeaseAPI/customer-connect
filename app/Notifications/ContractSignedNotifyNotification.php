<?php

namespace App\Notifications;

use App\Events\ContractSignedNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractSignedNotifyNotification extends Notification
{
    use Queueable;
    public function __construct(public ContractSignedNotify $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'contract_signed','message'=>'Contract signed'];}
}
