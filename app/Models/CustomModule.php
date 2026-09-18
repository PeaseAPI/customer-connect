<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomModule extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'icon', 'slug', 'menu_position',
        'sort_order', 'fields', 'list_columns', 'filters',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'fields' => 'array',
        'list_columns' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(CustomModuleData::class);
    }
}
