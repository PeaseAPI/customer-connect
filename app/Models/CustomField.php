<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'field_name', 'field_type', 'field_options',
        'module', 'is_required', 'is_active',
    ];

    protected $casts = [
        'field_options' => 'array', 'is_required' => 'boolean', 'is_active' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'customizable_id')
            ->where('customizable_type', CustomField::class);
    }
}