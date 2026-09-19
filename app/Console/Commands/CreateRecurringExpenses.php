<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\ExpenseRecurring;
use App\Enums\ExpenseStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateRecurringExpenses extends Command
{
    protected $signature = 'cc:create-recurring-expenses';
    protected $description = 'Generate expenses from recurring rules';

    public function handle(): int
    {
        $today = now()->toDateString();

        $rules = ExpenseRecurring::where('status', 'active')
            ->whereDate('next_expense_date', '<=', $today)
            ->with('expense')
            ->get();

        $created = 0;
        foreach ($rules as $rule) {
            $source = $rule->expense;
            if (! $source) {
                continue;
            }

            $baseDate = $rule->next_expense_date ?: $today;

            Expense::create([
                'company_id' => $source->company_id,
                'item_name' => $source->item_name,
                'purchase_from' => $source->purchase_from,
                'purchase_date' => $baseDate,
                'amount' => $source->amount,
                'currency_id' => $source->currency_id,
                'category_id' => $source->category_id,
                'project_id' => $source->project_id,
                'user_id' => $source->user_id,
                'status' => ExpenseStatus::Pending->value,
                'billable' => $source->billable,
                'note' => $source->note,
                'created_by' => $source->created_by,
            ]);

            $interval = max(1, (int) $rule->interval);
            $next = $this->advance($baseDate, $rule->frequency, $interval);

            // 跳过停跑期间积累的过期周期, 落到下一个未来日期
            while ($next->toDateString() <= $today) {
                $next = $this->advance($next->toDateString(), $rule->frequency, $interval);
            }

            $rule->update(['next_expense_date' => $next->toDateString()]);
            $created++;
        }

        $this->info("Recurring expenses created: {$created}");
        return self::SUCCESS;
    }

    private function advance(string $date, ?string $frequency, int $interval): Carbon
    {
        return match ($frequency) {
            'daily' => Carbon::parse($date)->addDays($interval),
            'weekly' => Carbon::parse($date)->addWeeks($interval),
            'yearly' => Carbon::parse($date)->addYears($interval),
            default => Carbon::parse($date)->addMonths($interval),
        };
    }
}
