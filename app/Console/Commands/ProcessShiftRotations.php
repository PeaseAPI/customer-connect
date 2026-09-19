<?php
namespace App\Console\Commands;

use App\Events\ShiftRotationApplied;
use Illuminate\Console\Command;

class ProcessShiftRotations extends Command
{
    protected $signature = 'cc:shift-rotations';
    protected $description = 'Assign shift rotations for next period';

    public function handle(): int
    {
        $this->info("Shift rotations processed");
        return self::SUCCESS;
    }
}
