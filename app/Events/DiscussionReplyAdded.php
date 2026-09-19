<?php
namespace App\Events;
use App\Models\DiscussionReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class DiscussionReplyAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public DiscussionReply $reply) {}
}
