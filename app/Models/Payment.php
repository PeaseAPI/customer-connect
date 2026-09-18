<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'invoice_id', 'client_id', 'amount',
        'gateway', 'transaction_id', 'currency_id', 'status',
        'paid_on', 'note', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'paid_on' => 'date', 'gateway' => PaymentGateway::class,
        'status' => PaymentStatus::class,
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
