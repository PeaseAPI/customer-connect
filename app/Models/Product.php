<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'price',
        'category_id',
        'sub_category_id',
        'tax_id',
        'unit_id',
        'sku',
        'added_by',
        'last_updated_by',
        'is_active',
    ];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class, 'category_id'); }
    public function subCategory(): BelongsTo { return $this->belongsTo(ProductSubCategory::class, 'sub_category_id'); }
    public function tax(): BelongsTo { return $this->belongsTo(TaxSetting::class, 'tax_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}