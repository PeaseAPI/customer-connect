<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Leave $leave) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'leave_request',
            'leave_id' => $this->leave->id,
            'employee_name' => $this->leave->employee?->name,
            'leave_type' => $this->leave->leaveType?->name,
            'duration' => $this->leave->duration,
            'message' => "{$this->leave->employee?->name} requested {$this->leave->duration} days of leave",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
