<?php

namespace App\Console\Commands;

use App\Events\ShiftRotationApplied;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftRotation;
use Illuminate\Console\Command;

class ProcessShiftRotations extends Command
{
    protected $signature = 'cc:shift-rotations';
    protected $description = 'Assign shift rotations for next period';

    public function handle(): int
    {
        $today = today();
        $employees = Employee::where('status', 'active')->get();
        $seeded = 0;
        $rotated = 0;

        foreach ($employees as $employee) {
            $shiftIds = Shift::where('company_id', $employee->company_id)->orderBy('id')->pluck('id');
            if ($shiftIds->isEmpty()) {
                continue;
            }

            $rotation = ShiftRotation::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            // 首次为员工播种当前班次周期
            if (! $rotation) {
                ShiftRotation::create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftIds->first(),
                    'period_start' => $today->toDateString(),
                    'period_end' => $today->copy()->addDays(13)->toDateString(),
                    'status' => 'active',
                ]);
                $seeded++;
                continue;
            }

            // 周期结束则轮换到同公司下一个班次
            if ($rotation->period_end->lt($today)) {
                $currentIndex = $shiftIds->search($rotation->shift_id);
                $nextIndex = ($currentIndex === false) ? 0 : ($currentIndex + 1) % $shiftIds->count();

                $newRotation = ShiftRotation::create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftIds[$nextIndex],
                    'period_start' => $today->toDateString(),
                    'period_end' => $today->copy()->addDays(13)->toDateString(),
                    'status' => 'active',
                ]);
                $rotation->update(['status' => 'completed']);
                event(new ShiftRotationApplied($newRotation));
                $rotated++;
            }
        }

        $this->info("Shift rotations - seeded: {$seeded}, rotated: {$rotated}");
        return self::SUCCESS;
    }
}
