<?php

namespace App\Notifications;

use App\Events\EstimateAccepted;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstimateAcceptedNotification extends Notification
{
    use Queueable;
    public function __construct(public EstimateAccepted $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'estimate_accepted','message'=>'Estimate accepted'];}
}
