<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdprSetting extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'gdpr_enable', 'privacy_policy', 'terms_and_conditions',
        'show_consent_on_signup', 'allow_right_to_be_forgotten', 'allow_data_export',
    ];

    protected $casts = [
        'gdpr_enable' => 'boolean',
        'show_consent_on_signup' => 'boolean',
        'allow_right_to_be_forgotten' => 'boolean',
        'allow_data_export' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
