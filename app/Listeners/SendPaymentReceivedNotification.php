<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Notifications\PaymentReceivedNotification;
use App\Models\User;

class SendPaymentReceivedNotification
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $invoice = $payment->invoice;
        if ($invoice && $invoice->owner_id) {
            $owner = User::find($invoice->owner_id);
            if ($owner) {
                $owner->notify(new PaymentReceivedNotification($payment));
            }
        }
    }
}
