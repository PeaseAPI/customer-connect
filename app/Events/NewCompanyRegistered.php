<?php
namespace App\Events;
use App\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class NewCompanyRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Company $company) {}
}
