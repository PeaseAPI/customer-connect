<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasCompanyScope, SoftDeletes;

    protected $fillable = [
        'company_id', 'asset_name', 'asset_code', 'category', 'description',
        'purchase_date', 'purchase_cost', 'current_value', 'useful_life_months',
        'depreciation_rate', 'status', 'allocated_to', 'allocated_date', 'return_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'allocated_date' => 'date',
        'return_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'useful_life_months' => 'integer',
    ];

    public function allocatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(AssetMaintenanceRecord::class);
    }
}
