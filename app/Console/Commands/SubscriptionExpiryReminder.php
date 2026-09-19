<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class SubscriptionExpiryReminder extends Command
{
    protected $signature = 'cc:subscription-expiry-reminders';
    protected $description = 'Remind about expiring subscriptions';

    public function handle(): int
    {
        $limit = now()->addDays(7)->toDateString();

        $subscriptions = Subscription::whereIn('status', ['active', 'trial'])
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<=', $limit)
            ->get();

        $reminded = 0;
        foreach ($subscriptions as $subscription) {
            $admins = User::where('company_id', $subscription->company_id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new EmployeeReminderNotification('订阅即将到期提醒', [
                    'subscription_id' => $subscription->id,
                    'package_id' => $subscription->package_id,
                    'ends_at' => (string) $subscription->ends_at,
                ]));
            }
            $reminded++;
        }

        $this->info("Subscription expiry reminders sent for {$reminded} subscriptions");
        return self::SUCCESS;
    }
}
