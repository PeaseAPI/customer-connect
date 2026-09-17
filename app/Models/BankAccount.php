<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'bank_name', 'branch_name', 'account_name', 'account_number',
        'account_type', 'sort_code', 'currency_id', 'contact_number',
        'opening_balance', 'bank_balance', 'bank_logo', 'status',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'bank_balance' => 'decimal:2',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}