<?php

namespace App\Jobs;

use App\Enums\CompanyStatus;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupExpiredSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // 查找所有过期的订阅
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);

            // 停用对应的公司
            if ($subscription->company) {
                $subscription->company->update(['status' => CompanyStatus::Expired]);
            }
        }

        // 即将到期的订阅提醒（7天内）
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(7))
            ->where('end_date', '>=', now())
            ->with('company')
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            // 这里可以发送提醒通知
            // $subscription->company->notify(new SubscriptionExpiringNotification($subscription));
        }
    }
}
