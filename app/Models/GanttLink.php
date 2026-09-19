<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class GanttLink extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'source_task_id',
        'target_task_id',
        'type', // 0=finish-to-start, 1=start-to-start, 2=finish-to-finish, 3=start-to-finish
        'lag',
    ];

    public function sourceTask()
    {
        return $this->belongsTo(Task::class, 'source_task_id');
    }

    public function targetTask()
    {
        return $this->belongsTo(Task::class, 'target_task_id');
    }
}
