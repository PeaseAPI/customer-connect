<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitType extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = ['company_id', 'unit_type'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
