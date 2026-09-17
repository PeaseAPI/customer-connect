<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'project_id',
        'invoice_number',
        'sub_total',
        'discount',
        'discount_type',
        'total',
        'tax',
        'currency_id',
        'status',
        'date',
        'due_date',
        'note',
        'recurring',
        'recurring_cycle',
        'recurring_next_date',
        'hash',
        'sent_on',
        'created_by',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2', 'discount' => 'decimal:2',
        'total' => 'decimal:2', 'tax' => 'decimal:2',
        'date' => 'date', 'due_date' => 'date',
        'recurring_next_date' => 'date', 'sent_on' => 'date',
        'status' => InvoiceStatus::class,
    ];

    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
