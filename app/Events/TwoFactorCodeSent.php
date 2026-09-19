<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $user = null, public string $code = '') {}
}
