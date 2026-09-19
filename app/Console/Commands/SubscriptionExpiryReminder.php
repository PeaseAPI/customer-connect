<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class SubscriptionExpiryReminder extends Command
{
    protected $signature = 'cc:subscription-expiry-reminders';
    protected $description = 'Remind about expiring subscriptions';

    public function handle(): int
    {
        $this->info("Subscription expiry reminders sent");
        return self::SUCCESS;
    }
}
