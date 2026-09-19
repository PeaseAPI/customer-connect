<?php
namespace App\Console\Commands;

use App\Models\Invoice;
use App\Events\InvoiceReminder;
use App\Events\InvoiceReminderAfter;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    protected $signature = 'cc:invoice-reminders';
    protected $description = 'Send reminders for due and overdue invoices';

    public function handle(): int
    {
        $companyColumn = 'company_id';
        $dueInvoices = Invoice::where('status', 'sent')
            ->where('due_date', '<=', now()->addDays(3))
            ->where('due_date', '>=', now())
            ->get();
        $overdueInvoices = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->get();

        foreach ($dueInvoices as $invoice) {
            event(new InvoiceReminder($invoice));
        }
        foreach ($overdueInvoices as $invoice) {
            event(new InvoiceReminderAfter($invoice));
        }

        $this->info("Due reminders: {$dueInvoices->count()}, Overdue reminders: {$overdueInvoices->count()}");
        return self::SUCCESS;
    }
}
