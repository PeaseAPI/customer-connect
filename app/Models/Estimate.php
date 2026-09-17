<?php

namespace App\Models;

use App\Enums\EstimateStatus;
use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'estimate_number',
        'sub_total',
        'discount',
        'discount_type',
        'total',
        'tax',
        'currency_id',
        'status',
        'date',
        'valid_till',
        'note',
        'hash',
        'sent_on',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2', 'discount' => 'decimal:2',
        'total' => 'decimal:2', 'tax' => 'decimal:2',
                'date' => 'date', 'valid_till' => 'date', 'sent_on' => 'date',
        'status' => EstimateStatus::class,
    ];

    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function items(): HasMany { return $this->hasMany(EstimateItem::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class, 'currency_id'); }
}
