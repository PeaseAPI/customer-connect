<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Leave $leave, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $statusText = match ($this->status) {
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'cancelled' => '已取消',
            default => $this->status,
        };

        return [
            'type' => 'leave_status',
            'leave_id' => $this->leave->id,
            'status' => $this->status,
            'message' => "您的请假申请{$statusText}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
