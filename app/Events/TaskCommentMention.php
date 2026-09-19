<?php
namespace App\Events;
use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class TaskCommentMention
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public ?TaskComment $comment = null, public $mentionedUserId = null) {}
}