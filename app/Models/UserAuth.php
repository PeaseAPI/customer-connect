<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAuth extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'user_id', 'company_id', 'is_superadmin', 'last_login',
    ];

    protected $casts = [
        'is_superadmin' => 'boolean',
        'last_login' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
