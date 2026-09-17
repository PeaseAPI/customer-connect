<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id',
        'project_id',
        'title',
        'description',
        'assign_to',
        'status',
        'priority',
        'start_date',
        'due_date',
        'milestone_id',
        'parent_task_id',
        'category_id',
        'board_column',
        'is_pinned',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date', 'due_date' => 'date', 'recurring_next_date' => 'date',
        'is_recurring' => 'boolean', 'is_pinned' => 'boolean',
        'completion_percent' => 'integer', 'board_column' => 'integer',
        'status' => TaskStatus::class, 'priority' => Priority::class,
    ];

        public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assign_to'); }
    public function milestone(): BelongsTo { return $this->belongsTo(Milestone::class); }
    public function parentTask(): BelongsTo { return $this->belongsTo(Task::class, 'parent_task_id'); }
    public function subTasks(): HasMany { return $this->hasMany(Task::class, 'parent_task_id'); }
    public function subTaskItems(): HasMany { return $this->hasMany(SubTask::class); }
    public function timelogs(): HasMany { return $this->hasMany(Timelog::class); }
    public function labels(): BelongsToMany { return $this->belongsToMany(TaskLabel::class, 'task_label_task'); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'task_users'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function category(): BelongsTo { return $this->belongsTo(TaskCategory::class, 'category_id'); }
}
