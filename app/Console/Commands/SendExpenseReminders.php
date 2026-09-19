<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class SendExpenseReminders extends Command
{
    protected $signature = 'cc:expense-reminders';
    protected $description = 'Send reminders for pending expense approvals';

    public function handle(): int
    {
        $expenses = Expense::where('status', 'pending')->get();

        $reminded = 0;
        foreach ($expenses as $expense) {
            $approvers = User::where('company_id', $expense->company_id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                ->get();

            foreach ($approvers as $approver) {
                $approver->notify(new EmployeeReminderNotification('费用审批提醒', [
                    'expense_id' => $expense->id,
                    'item_name' => $expense->item_name,
                    'amount' => (string) $expense->amount,
                ]));
            }
            $reminded++;
        }

        $this->info("Expense approval reminders sent for {$reminded} expenses");
        return self::SUCCESS;
    }
}
