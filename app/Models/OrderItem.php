<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasCompanyScope;

        protected $fillable = [
        'company_id', 'order_id', 'product_id', 'item_name', 'item_summary',
        'type', 'sku', 'quantity', 'unit_price', 'amount', 'tax_id',
        'taxes', 'hsn_sac_code', 'unit_id', 'field_order',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'amount' => 'decimal:2'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}