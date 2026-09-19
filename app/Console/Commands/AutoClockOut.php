<?php
namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class AutoClockOut extends Command
{
    protected $signature = 'cc:auto-clock-out';
    protected $description = 'Auto clock-out users who forgot to clock out';

    public function handle(): int
    {
        $attendances = Attendance::whereNull("clock_out_time")->whereDate("clock_in_time", today())->get(); foreach($attendances as $a){ $a->update(["clock_out_time"=>now()->setTime(23,59,59),"is_auto_clock_out"=>true]); } $this->info("Auto clocked out: {$attendances->count()}");
        return self::SUCCESS;
    }
}
