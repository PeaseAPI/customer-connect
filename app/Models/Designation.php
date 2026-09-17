<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = ['company_id', 'designation_name', 'parent_id'];

    public function parent(): BelongsTo { return $this->belongsTo(Designation::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Designation::class, 'parent_id'); }
    public function users(): HasMany { return $this->hasMany(User::class); }
}
