<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomModuleData extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'custom_module_id', 'data',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function customModule(): BelongsTo
    {
        return $this->belongsTo(CustomModule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
