<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflinePaymentMethod extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'method_name', 'description', 'bank_name',
        'bank_account_number', 'bank_code', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
