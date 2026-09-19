<?php
namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class MarkInactiveEmployees extends Command
{
    protected $signature = 'cc:mark-inactive-employees';
    protected $description = 'Mark employees as inactive if past end date';

    public function handle(): int
    {
        $employees = Employee::where("end_date","<",now())->where("status","active")->get(); foreach($employees as $e){ $e->update(["status"=>"inactive"]); } $this->info("Marked inactive: {$employees->count()}");
        return self::SUCCESS;
    }
}
