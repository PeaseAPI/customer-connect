<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'item_name',
        'item_summary',
        'quantity',
        'unit_price',
        'amount',
        'tax_id',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'amount' => 'decimal:2'];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function tax(): BelongsTo { return $this->belongsTo(TaxSetting::class); }
}
