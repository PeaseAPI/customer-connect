<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasCompanyScope, SoftDeletes;

    protected $fillable = [
        'company_id', 'vendor_name', 'contact_person', 'email',
        'phone', 'address', 'category', 'note', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
