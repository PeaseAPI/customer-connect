<?php
namespace App\Console\Commands;

use App\Models\ActiveTimer;
use Illuminate\Console\Command;

class AutoStopTimers extends Command
{
    protected $signature = 'cc:auto-stop-timers';
    protected $description = 'Auto stop active timers at end of day';

    public function handle(): int
    {
        $timers = ActiveTimer::where("is_running",true)->get(); foreach($timers as $t){ $t->update(["is_running"=>false,"ended_at"=>now()->setTime(23,59,59)]); } $this->info("Timers stopped: {$timers->count()}");
        return self::SUCCESS;
    }
}
