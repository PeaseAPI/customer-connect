<?php

namespace App\Jobs;

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

            if (!$recurring || $recurring->status !== 'active') {
                return;
            }

            // 检查是否该生成新发票
            if (!$recurring->shouldGenerateNow()) {
                return;
            }

            // 生成发票
            $invoice = $recurring->generateInvoice();

            Log::info("循环发票 #{$recurring->id} 生成新发票 #{$invoice->id}");

            // 更新下次生成日期
            $recurring->updateNextDate();
        } catch (\Exception $e) {
            Log::error('处理循环发票失败', [
                'recurring_id' => $this->recurringInvoiceId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
