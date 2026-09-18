<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadContact extends Model
{
    use HasCompanyScope, HasFactory;

        protected $fillable = [
        'company_id',
        'lead_id',
        'contact_name',
        'email',
        'phone',
        'is_primary',
        'added_by',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
