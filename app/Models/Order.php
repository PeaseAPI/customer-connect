<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
            use HasCompanyScope, HasFactory;

        protected $fillable = [
        'company_id', 'client_id', 'order_number', 'original_order_number', 'status',
        'sub_total', 'discount', 'discount_type', 'total', 'due_amount', 'tax',
        'currency_id', 'date', 'note', 'show_shipping_address',
        'company_address_id', 'project_id', 'created_by', 'last_updated_by',
    ];

        protected $casts = [
        'sub_total' => 'decimal:2', 'discount' => 'decimal:2',
        'total' => 'decimal:2', 'due_amount' => 'decimal:2',
        'tax' => 'decimal:2', 'date' => 'date',
    ];

        public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
}