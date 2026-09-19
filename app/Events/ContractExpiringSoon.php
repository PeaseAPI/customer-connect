<?php
namespace App\Events;
use App\Models\Contract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class ContractExpiringSoon
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Contract $contract) {}
}
