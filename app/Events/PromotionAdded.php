<?php
namespace App\Events;
use App\Models\Promotion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PromotionAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Promotion $promotion) {}
}
