<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurposeConsentUser extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'purpose_consent_id', 'user_id', 'ip_address', 'user_agent', 'consented_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
    ];

    public function purposeConsent(): BelongsTo { return $this->belongsTo(PurposeConsent::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
