<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurposeConsent extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'description', 'is_default', 'is_active', 'allow_opt_out',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'allow_opt_out' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function consentUsers(): HasMany { return $this->hasMany(PurposeConsentUser::class); }
    public function consentLeads(): HasMany { return $this->hasMany(PurposeConsentLead::class); }
}
