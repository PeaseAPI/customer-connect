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

// 每小时处理循环Invoice
Schedule::call(function () {
    $activeRecurrings = RecurringInvoice::where('status', 'active')
        ->where('next_date', '<=', now())
        ->get();
    foreach ($activeRecurrings as $recurring) {
        ProcessRecurringInvoice::dispatch($recurring->id);
    }
})->hourly()->name('process-recurring-invoices');

// 每日处理Recurring task和循环事件
Schedule::command('cc:process-recurring')->dailyAt('06:00')->name('process-recurring-items');

// 每5分钟处理Event reminder
Schedule::call(function () {
    app(\App\Services\Event\RecurringEventService::class)->processReminders();
})->everyFiveMinutes()->name('process-event-reminders');

// ============================================================
// P0-3: 补全的定时任务调度
// ============================================================

// 每日09:00 发票到期/逾期提醒
Schedule::command('cc:invoice-reminders')->dailyAt('09:00')->name('invoice-reminders');

// 每日08:00 任务到期提醒
Schedule::command('cc:task-reminders')->dailyAt('08:00')->name('task-reminders');

// 每日08:00 项目到期提醒
Schedule::command('cc:project-reminders')->dailyAt('08:00')->name('project-reminders');

// 每日08:00 生日提醒
Schedule::command('cc:birthday-reminders')->dailyAt('08:00')->name('birthday-reminders');

// 每日09:00 合同到期提醒
Schedule::command('cc:contract-expiry-reminders')->dailyAt('09:00')->name('contract-expiry-reminders');

// 每日23:59 自动下班打卡
Schedule::command('cc:auto-clock-out')->dailyAt('23:59')->name('auto-clock-out');

// 每日23:59 自动停止计时器
Schedule::command('cc:auto-stop-timers')->dailyAt('23:59')->name('auto-stop-timers');

// 每日08:30 考勤打卡提醒
Schedule::command('cc:attendance-reminders')->dailyAt('08:30')->name('attendance-reminders');

// 每周一09:00 周工时表提醒
Schedule::command('cc:weekly-timesheet-reminders')->weeklyOn(1, '09:00')->name('weekly-timesheet-reminders');

// 每日03:00 清理已读通知
Schedule::command('cc:clean-notifications')->dailyAt('03:00')->name('clean-notifications');

// 每周日凌晨03:00 清理旧日志
Schedule::command('cc:clean-logs')->weeklyOn(0, '03:00')->name('clean-logs');

// 每日 标记离职员工
Schedule::command('cc:mark-inactive-employees')->dailyAt('01:00')->name('mark-inactive-employees');

// 每日08:00 线索跟进提醒
Schedule::command('cc:follow-up-reminders')->dailyAt('08:00')->name('follow-up-reminders');

// 每小时 事件提醒
Schedule::command('cc:event-reminders')->hourly()->name('event-reminders');

// 每日06:00 循环费用
Schedule::command('cc:create-recurring-expenses')->dailyAt('06:00')->name('create-recurring-expenses');

// 每日06:00 循环发票
Schedule::command('cc:create-recurring-invoices')->dailyAt('06:00')->name('create-recurring-invoices');

// 每日02:00 数据库备份
Schedule::command('cc:database-backup')->dailyAt('02:00')->name('database-backup');

// 每日03:00 清理过期备份
Schedule::command('cc:cleanup-backups')->dailyAt('03:00')->name('cleanup-backups');

// 每周一 排班轮转
Schedule::command('cc:shift-rotations')->weeklyOn(1, '00:00')->name('shift-rotations');

// 每日09:00 费用审批提醒
Schedule::command('cc:expense-reminders')->dailyAt('09:00')->name('expense-reminders');

// 每日09:00 员工文档到期提醒
Schedule::command('cc:employee-document-expiry')->dailyAt('09:00')->name('employee-document-expiry');

// 每日 订阅到期提醒
Schedule::command('cc:subscription-expiry-reminders')->dailyAt('08:00')->name('subscription-expiry-reminders');

// 每周 删除垃圾公司
Schedule::command('cc:delete-spam-companies')->weeklyOn(0, '03:00')->name('delete-spam-companies');
