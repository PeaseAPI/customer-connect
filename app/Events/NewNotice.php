<?php
namespace App\Events;
use App\Models\Notice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class NewNotice
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Notice $notice) {}
}
