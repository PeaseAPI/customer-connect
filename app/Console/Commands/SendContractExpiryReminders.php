<?php
namespace App\Console\Commands;

use App\Models\Contract; use App\Events\ContractExpiringSoon;
use Illuminate\Console\Command;

class SendContractExpiryReminders extends Command
{
    protected $signature = 'cc:contract-expiry-reminders';
    protected $description = 'Send reminders for expiring contracts';

    public function handle(): int
    {
        $contracts = Contract::where("end_date","<=",now()->addDays(30))->where("end_date",">=",now())->whereNotIn("status",["terminated","expired"])->get(); foreach($contracts as $c){ event(new ContractExpiringSoon($c)); } $this->info("Contract expiry reminders: {$contracts->count()}");
        return self::SUCCESS;
    }
}
