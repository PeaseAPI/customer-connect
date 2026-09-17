<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Notifications\ContractExpiringNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringContracts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $days = 30) {}

    public function handle(): void
    {
        $contracts = Contract::where('status', 'active')
            ->where('end_date', '<=', now()->addDays($this->days))
            ->where('end_date', '>=', now())
            ->with(['owner', 'client'])
            ->get();

        foreach ($contracts as $contract) {
            if ($contract->owner) {
                $contract->owner->notify(new ContractExpiringNotification($contract, $this->days));
            }
        }
    }
}
