<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMention
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $mention = null) {}
}
