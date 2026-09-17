<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Traits\HasCompanyScope;
use App\Traits\HasComments;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id',
        'project_name',
        'project_summary',
        'client_id',
        'start_date',
        'deadline',
        'status',
        'priority',
        'budget',
        'billing_type',
        'hourly_rate',
        'category_id',
        'completion_percent',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'start_date' => 'date', 'deadline' => 'date',
        'budget' => 'decimal:2', 'hourly_rate' => 'decimal:2',
        'completion_percent' => 'integer',
        'status' => ProjectStatus::class, 'priority' => Priority::class,
    ];

        public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function category(): BelongsTo { return $this->belongsTo(ProjectCategory::class, 'category_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function members(): BelongsToMany {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role', 'hourly_rate')->withTimestamps();
    }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function milestones(): HasMany { return $this->hasMany(Milestone::class); }
    public function timelogs(): HasMany { return $this->hasMany(Timelog::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'last_updated_by'); }
}
