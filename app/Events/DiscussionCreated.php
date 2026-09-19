<?php
namespace App\Events;
use App\Models\Discussion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class DiscussionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Discussion $discussion) {}
}
