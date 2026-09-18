<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomLink extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'title', 'url', 'icon', 'target',
        'section', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
