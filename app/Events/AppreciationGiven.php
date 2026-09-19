<?php
namespace App\Events;
use App\Models\Appreciation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class AppreciationGiven
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Appreciation $appreciation) {}
}
