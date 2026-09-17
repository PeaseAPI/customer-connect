<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use App\Models\User;

class SendTaskAssignedNotification
{
    public function handle(TaskAssigned $event): void
    {
        $assignee = User::find($event->assigneeId);
        if ($assignee) {
            $assignee->notify(new TaskAssignedNotification($event->task));
        }
    }
}
