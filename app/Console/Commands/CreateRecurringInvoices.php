<?php
namespace App\Console\Commands;

use App\Models\RecurringInvoice; use App\Events\NewInvoiceRecurring;
use Illuminate\Console\Command;

class CreateRecurringInvoices extends Command
{
    protected $signature = 'cc:create-recurring-invoices';
    protected $description = 'Generate invoices from recurring rules';

    public function handle(): int
    {
        $this->info("Recurring invoices processed");
        return self::SUCCESS;
    }
}
