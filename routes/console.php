<?php

use App\Jobs\CheckExpiringContracts;
use App\Jobs\CleanupExpiredSubscriptions;
use App\Jobs\ProcessRecurringInvoice;
use App\Models\RecurringInvoice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily check for expiring contracts
Schedule::job(new CheckExpiringContracts(30))->dailyAt('09:00')->name('check-expiring-contracts');

// 每日清理过期订阅
Schedule::job(new CleanupExpiredSubscriptions)->dailyAt('01:00')->name('cleanup-expired-subscriptions');

// 每小时处理循环发票
Schedule::call(function () {
    $activeRecurrings = RecurringInvoice::where('status', 'active')
        ->where('next_date', '<=', now())
        ->get();
    foreach ($activeRecurrings as $recurring) {
        ProcessRecurringInvoice::dispatch($recurring->id);
    }
})->hourly()->name('process-recurring-invoices');

// 每日处理循环任务和循环事件
Schedule::command('cc:process-recurring')->dailyAt('06:00')->name('process-recurring-items');

// 每5分钟处理事件提醒
Schedule::call(function () {
    app(\App\Services\Event\RecurringEventService::class)->processReminders();
})->everyFiveMinutes()->name('process-event-reminders');
