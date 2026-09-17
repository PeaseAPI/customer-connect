<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceLog extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'recurring_invoice_id', 'invoice_id', 'generated_on', 'status',
    ];

    protected $casts = [
        'generated_on' => 'date',
    ];

    public function recurringInvoice(): BelongsTo { return $this->belongsTo(RecurringInvoice::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
