<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'currency_name', 'currency_code', 'currency_symbol',
        'exchange_rate', 'is_active',
    ];

    protected $casts = ['exchange_rate' => 'decimal:6', 'is_active' => 'boolean'];
}