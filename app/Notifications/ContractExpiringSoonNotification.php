<?php

namespace App\Notifications;

use App\Events\ContractExpiringSoon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiringSoonNotification extends Notification
{
    use Queueable;
    public function __construct(public ContractExpiringSoon $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'contract_expiring','message'=>'Contract expiring soon'];}
}
