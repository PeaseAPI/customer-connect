<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Contract $contract, public int $days) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'contract_expiring',
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'client_name' => $this->contract->client?->name,
            'end_date' => $this->contract->end_date?->toDateString(),
            'message' => "合同「{$this->contract->title}」将于{$this->days}天内到期",
        ];
    }
}
