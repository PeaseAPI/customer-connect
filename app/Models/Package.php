<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'max_employees',
        'max_storage_mb',
        'is_free',
        'trial_days',
        'is_default',
        'is_recommended',
        'sort_order',
        'status',
        'ai_model_tokens',
        'modules',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2', 'annual_price' => 'decimal:2',
        'max_employees' => 'integer', 'max_storage_mb' => 'integer',
        'is_free' => 'boolean', 'is_trial' => 'boolean', 'trial_days' => 'integer',
        'is_default' => 'boolean', 'is_recommended' => 'boolean',
        'sort_order' => 'integer', 'ai_model_tokens' => 'integer',
        'is_active' => 'boolean', 'modules' => 'array',
    ];

    public function companies(): HasMany { return $this->hasMany(Company::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
}