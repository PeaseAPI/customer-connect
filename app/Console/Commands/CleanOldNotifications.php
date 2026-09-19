<?php
namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanOldNotifications extends Command
{
    protected $signature = 'cc:clean-notifications';
    protected $description = 'Remove old read notifications';

    public function handle(): int
    {
        \DB::table("notifications")->where("read_at","<",now()->subDays(90))->delete(); $this->info("Old notifications cleaned");
        return self::SUCCESS;
    }
}
