<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateItem extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'estimate_id',
        'item_name',
        'quantity',
        'unit_price',
        'amount',
        'tax_id',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'amount' => 'decimal:2'];

    public function estimate(): BelongsTo { return $this->belongsTo(Estimate::class); }
}
