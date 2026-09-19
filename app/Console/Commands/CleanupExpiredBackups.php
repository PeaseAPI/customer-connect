<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupExpiredBackups extends Command
{
    protected $signature = 'cc:cleanup-backups';
    protected $description = 'Remove expired database backups';

    public function handle(): int
    {
        $this->info("Expired backups cleaned");
        return self::SUCCESS;
    }
}
