<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTimeLogBreak extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'project_time_log_id', 'break_start', 'break_end', 'break_minutes',
    ];

    public function timeLog(): BelongsTo { return $this->belongsTo(ProjectTimeLog::class); }
}
