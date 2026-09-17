<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAddress extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'address', 'is_default', 'tax_number', 'tax_name', 'location',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
