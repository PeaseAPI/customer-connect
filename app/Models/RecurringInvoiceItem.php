<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceItem extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'recurring_invoice_id', 'item_name', 'item_summary',
        'type', 'quantity', 'unit_price', 'amount', 'tax_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function recurringInvoice(): BelongsTo { return $this->belongsTo(RecurringInvoice::class); }
}
