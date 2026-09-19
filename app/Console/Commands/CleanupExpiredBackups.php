<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CleanupExpiredBackups extends Command
{
    protected $signature = 'cc:cleanup-backups';
    protected $description = 'Remove expired database backups (delegates to spatie/laravel-backup)';

    public function handle(): int
    {
        $code = Artisan::call('backup:clean');
        $this->line(trim(Artisan::output()));

        if ($code !== 0) {
            $this->error('Backup cleanup failed');
            return self::FAILURE;
        }

        $this->info('Expired backups cleaned');
        return self::SUCCESS;
    }
}
