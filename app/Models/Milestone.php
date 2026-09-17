<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'project_id',
        'milestone_title',
        'milestone_cost',
        'status',
        'start_date',
        'deadline',
    ];

    protected $casts = ['start_date' => 'date', 'deadline' => 'date', 'milestone_cost' => 'decimal:2', 'status' => MilestoneStatus::class];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
