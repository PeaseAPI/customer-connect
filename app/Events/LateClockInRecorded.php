<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LateClockInRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $clockIn = null) {}
}
