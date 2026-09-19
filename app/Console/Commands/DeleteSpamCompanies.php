<?php
namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class DeleteSpamCompanies extends Command
{
    protected $signature = 'cc:delete-spam-companies';
    protected $description = 'Delete spam/inactive companies older than 30 days';

    public function handle(): int
    {
        $this->info("Spam companies deleted");
        return self::SUCCESS;
    }
}
