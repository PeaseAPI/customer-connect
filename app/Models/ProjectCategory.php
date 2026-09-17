<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'category_name', 'color',
        'added_by', 'last_updated_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'category_id');
    }
}
