<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSetting extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'invoice_prefix', 'invoice_digits', 'estimate_prefix',
        'credit_note_prefix', 'invoice_number_separator', 'next_invoice_number',
        'next_estimate_number', 'next_credit_note_number', 'template', 'due_after',
        'currency_format', 'decimal_separator', 'thousand_separator',
        'show_client_note', 'show_item_tax', 'invoice_note', 'estimate_note',
        'credit_note_note', 'terms_and_conditions',
    ];

    protected $casts = [
        'invoice_digits' => 'integer',
        'show_client_note' => 'boolean',
        'show_item_tax' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
