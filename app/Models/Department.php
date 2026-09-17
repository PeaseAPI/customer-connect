<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = ['company_id', 'department_name', 'parent_id', 'department_head'];

    public function parent(): BelongsTo { return $this->belongsTo(Department::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Department::class, 'parent_id'); }
    public function head(): BelongsTo { return $this->belongsTo(User::class, 'department_head'); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'department_users'); }
}
