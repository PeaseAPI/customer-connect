<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DatabaseBackup extends Command
{
    protected $signature = 'cc:database-backup';
    protected $description = 'Perform database backup (delegates to spatie/laravel-backup)';

    public function handle(): int
    {
        $code = Artisan::call('backup:run', ['--only-db' => true]);
        $this->line(trim(Artisan::output()));

        if ($code !== 0) {
            $this->error('Database backup failed');
            return self::FAILURE;
        }

        $this->info('Database backup completed');
        return self::SUCCESS;
    }
}
