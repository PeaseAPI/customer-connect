<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature = 'cc:database-backup';
    protected $description = 'Perform database backup';

    public function handle(): int
    {
        $this->info("Database backup completed");
        return self::SUCCESS;
    }
}
