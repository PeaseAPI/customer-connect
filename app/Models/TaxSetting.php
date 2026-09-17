<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = ['company_id', 'tax_name', 'tax_percent', 'is_active'];

    protected $casts = ['tax_percent' => 'decimal:2', 'is_active' => 'boolean'];
}