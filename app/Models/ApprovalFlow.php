<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalFlow extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id',
        'external_type',
        'external_template_id',
    ];

    protected $casts = ['steps' => 'array', 'is_active' => 'boolean', 'config' => 'array'];

    public function requests(): HasMany { return $this->hasMany(ApprovalRequest::class); }
}
