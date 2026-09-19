<?php
namespace App\Events;
use App\Models\TaskComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class TaskCommentAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public TaskComment $comment) {}
}
