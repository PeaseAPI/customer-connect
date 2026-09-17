<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDetail extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'user_id',
        'category_id',
        'sub_category_id',
        'address',
        'note',
        'shipping_address',
    ];

    protected $casts = ['note' => 'encrypted'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
