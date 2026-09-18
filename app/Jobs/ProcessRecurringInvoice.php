<?php

namespace App\Jobs;

use App\Enums\RecurringStatus;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRecurringInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $recurringInvoiceId
    ) {}

    public function handle(): void
    {
        try {
            $recurring = \App\Models\RecurringInvoice::find($this->recurringInvoiceId);

                        if (!$recurring || $recurring->status !== RecurringStatus::Active) {
                return;
            }

            // 检查YesNo该生成新Invoice
            if (!$recurring->shouldGenerateNow()) {
                return;
            }

            // 生成Invoice
            $invoice = $recurring->generateInvoice();

            Log::info("Recurring invoice #{$recurring->id} generated new invoice #{$invoice->id}");

            // 更新下次生成日期
            $recurring->updateNextDate();
        } catch (\Exception $e) {
            Log::error('Failed to process recurring invoice', [
                'recurring_id' => $this->recurringInvoiceId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
