<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTimeLog extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'project_id', 'task_id', 'user_id', 'log_date',
        'start_time', 'end_time', 'total_minutes', 'total_hours',
        'note', 'editor', 'billable', 'added_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'total_hours' => 'decimal:2',
        'billable' => 'boolean',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
    public function breaks(): HasMany { return $this->hasMany(ProjectTimeLogBreak::class); }
}
