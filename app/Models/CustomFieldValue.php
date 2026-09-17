<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'custom_field_id', 'customizable_type',
        'customizable_id', 'value',
    ];

    public function customizable(): MorphTo { return $this->morphTo(); }
    public function customField(): BelongsTo { return $this->belongsTo(CustomField::class); }
}