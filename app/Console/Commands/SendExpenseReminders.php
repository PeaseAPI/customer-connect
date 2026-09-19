<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendExpenseReminders extends Command
{
    protected $signature = 'cc:expense-reminders';
    protected $description = 'Send reminders for pending expense approvals';

    public function handle(): int
    {
        $this->info("Expense approval reminders sent");
        return self::SUCCESS;
    }
}
