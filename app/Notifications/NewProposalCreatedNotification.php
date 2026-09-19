<?php

namespace App\Notifications;

use App\Events\NewProposalCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewProposalCreatedNotification extends Notification
{
    use Queueable;
    public function __construct(public NewProposalCreated $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_proposal','message'=>'New proposal created'];}
}
