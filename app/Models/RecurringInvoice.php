<?php

namespace App\Models;

use App\Enums\RecurringStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringInvoice extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'client_id', 'project_id', 'currency_id',
        'invoice_number', 'frequency', 'interval', 'start_date',
        'end_date', 'next_invoice_date', 'sub_total', 'discount',
        'discount_type', 'total', 'tax', 'note', 'status',
        'enable_auto_pay', 'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_invoice_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'tax' => 'decimal:2',
        'enable_auto_pay' => 'boolean',
        'status' => RecurringStatus::class,
    ];

    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
    public function items(): HasMany { return $this->hasMany(RecurringInvoiceItem::class); }
    public function logs(): HasMany { return $this->hasMany(RecurringInvoiceLog::class); }
}
