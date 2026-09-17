<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timelog extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'project_id',
        'task_id',
        'user_id',
        'start_time',
        'end_time',
        'total_hours',
        'memo',
        'edited_by',
    ];

    protected $casts = [
        'start_time' => 'datetime', 'end_time' => 'datetime',
        'hours' => 'decimal:2', 'total_hours' => 'decimal:2',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function editor(): BelongsTo { return $this->belongsTo(User::class, 'edited_by'); }
}
