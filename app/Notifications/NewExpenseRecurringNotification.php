<?php

namespace App\Notifications;

use App\Events\NewExpenseRecurring;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewExpenseRecurringNotification extends Notification
{
    use Queueable;
    public function __construct(public NewExpenseRecurring $event){}
    public function via(object $n): array{return ['database'];}
    public function toDatabase(object $n): array{return ['type'=>'new_expense_recurring','message'=>'Recurring expense created'];}
}
