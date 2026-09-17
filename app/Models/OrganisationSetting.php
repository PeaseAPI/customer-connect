<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationSetting extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'company_name', 'company_email', 'company_phone',
        'logo', 'currency_id', 'timezone', 'date_format', 'time_format',
        'fiscal_year', 'leaves_start_from', 'active_theme', 'task_self',
        'lead_source', 'after_login',
    ];

    protected $casts = ['task_self' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}