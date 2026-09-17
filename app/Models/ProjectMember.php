<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'project_id', 'user_id', 'role', 'hourly_rate'];

    protected $casts = ['hourly_rate' => 'decimal:2'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}