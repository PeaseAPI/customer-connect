<?php
namespace App\Events;
use App\Models\Proposal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class NewProposalCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Proposal $proposal) {}
}
