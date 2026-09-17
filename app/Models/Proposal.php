<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use HasCompanyScope, SoftDeletes, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id', 'lead_id', 'deal_id', 'client_id', 'currency_id',
        'proposal_number', 'subject', 'hash', 'valid_till',
        'sub_total', 'total', 'discount', 'discount_type', 'calculate_tax',
        'status', 'invoice_convert', 'send_status', 'note', 'description',
        'ip_address', 'signature_approval', 'client_comment', 'last_viewed',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'valid_till' => 'date',
        'last_viewed' => 'datetime',
        'invoice_convert' => 'boolean',
        'send_status' => 'boolean',
        'signature_approval' => 'boolean',
    ];

    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function deal(): BelongsTo { return $this->belongsTo(Deal::class); }
    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function items(): HasMany { return $this->hasMany(ProposalItem::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}