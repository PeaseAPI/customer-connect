<?php
namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CleanOldLogs extends Command
{
    protected $signature = 'cc:clean-logs';
    protected $description = 'Remove old activity logs';

    public function handle(): int
    {
        \DB::table("activity_log")->where("created_at","<",now()->subDays(90))->delete(); $this->info("Old logs cleaned");
        return self::SUCCESS;
    }
}
