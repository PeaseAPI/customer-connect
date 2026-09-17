<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = ['company_id', 'team_name', 'added_by'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
    public function members(): BelongsToMany { return $this->belongsToMany(User::class, 'employee_teams', 'team_id', 'user_id')->withTimestamps(); }
}
