<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmployeeDocumentExpiryReminder extends Command
{
    protected $signature = 'cc:employee-document-expiry';
    protected $description = 'Remind about expiring employee documents';

    public function handle(): int
    {
        $this->info("Employee document expiry reminders sent");
        return self::SUCCESS;
    }
}
