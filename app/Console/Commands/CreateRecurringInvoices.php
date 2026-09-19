<?php

namespace App\Console\Commands;

use App\Events\NewInvoiceRecurring;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use App\Models\RecurringInvoiceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateRecurringInvoices extends Command
{
    protected $signature = 'cc:create-recurring-invoices';
    protected $description = 'Generate invoices from recurring rules';

    public function handle(): int
    {
        $today = now()->toDateString();

        $rules = RecurringInvoice::where('status', 'active')
            ->whereDate('next_invoice_date', '<=', $today)
            ->get();

        $created = 0;
        foreach ($rules as $rule) {
            $baseDate = $rule->next_invoice_date ?: $today;

            // 停跑期超过 end_date 则完结规则, 不再生成
            if ($rule->end_date && $baseDate > $rule->end_date) {
                $rule->update(['status' => 'completed']);
                continue;
            }

            $invoice = Invoice::create([
                'company_id' => $rule->company_id,
                'client_id' => $rule->client_id,
                'project_id' => $rule->project_id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'sub_total' => $rule->sub_total,
                'discount' => $rule->discount,
                'discount_type' => $rule->discount_type,
                'total' => $rule->total,
                'tax' => $rule->tax,
                'currency_id' => $rule->currency_id,
                'status' => 'draft',
                'date' => $baseDate,
                'due_date' => Carbon::parse($baseDate)->addDays(14)->toDateString(),
                'note' => $rule->note,
                'created_by' => $rule->added_by,
            ]);

            RecurringInvoiceLog::create([
                'company_id' => $rule->company_id,
                'recurring_invoice_id' => $rule->id,
                'invoice_id' => $invoice->id,
                'generated_on' => $baseDate,
                'status' => 'generated',
            ]);

            event(new NewInvoiceRecurring($invoice));

            $interval = max(1, (int) $rule->interval);
            $next = $this->advance($baseDate, $rule->frequency, $interval);

            // 跳过停跑期间积累的过期周期, 落到下一个未来日期
            while ($next->toDateString() <= $today) {
                $next = $this->advance($next->toDateString(), $rule->frequency, $interval);
            }

            // 下一期超出 end_date 则完结规则
            if ($rule->end_date && $next->toDateString() > $rule->end_date) {
                $rule->update(['next_invoice_date' => $next->toDateString(), 'status' => 'completed']);
            } else {
                $rule->update(['next_invoice_date' => $next->toDateString()]);
            }

            $created++;
        }

        $this->info("Recurring invoices created: {$created}");
        return self::SUCCESS;
    }

    private function nextInvoiceNumber(): string
    {
        return 'INV-' . now()->format('ymd') . '-' . strtoupper(substr((string) \Str::uuid(), 0, 8));
    }

    private function advance(string $date, ?string $frequency, int $interval): Carbon
    {
        return match ($frequency) {
            'daily' => Carbon::parse($date)->addDays($interval),
            'weekly' => Carbon::parse($date)->addWeeks($interval),
            'yearly', 'annually' => Carbon::parse($date)->addYears($interval),
            default => Carbon::parse($date)->addMonths($interval),
        };
    }
}
