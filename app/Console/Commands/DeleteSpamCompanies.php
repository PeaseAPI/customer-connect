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
        $companies = Company::where('status', 'inactive')
            ->whereNull('deleted_at')
            ->where('created_at', '<', now()->subDays(30))
            ->get();

        foreach ($companies as $company) {
            $company->delete();
        }

        $this->info("Spam companies deleted: {$companies->count()}");
        return self::SUCCESS;
    }
}
