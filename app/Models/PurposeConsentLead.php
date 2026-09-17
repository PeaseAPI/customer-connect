<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurposeConsentLead extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'purpose_consent_id', 'lead_id', 'ip_address', 'user_agent', 'consented_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
    ];

    public function purposeConsent(): BelongsTo { return $this->belongsTo(PurposeConsent::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
}
