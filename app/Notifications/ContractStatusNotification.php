<?php

namespace App\Notifications;

use App\Enums\ContractStatus;
use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ContractStatusNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Contract $contract, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
                $statusText = match ($this->status) {
            ContractStatus::Active->value => 'Effective',
            ContractStatus::Expired->value => 'Expired',
            ContractStatus::Canceled->value => 'Terminated',
            default => $this->status,
        };

        return [
            'type' => 'contract_status',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'status' => $this->status,
            'message' => "Contract "{$this->contract->title}" {$statusText}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
