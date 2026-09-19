<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'employee_code', 'name', 'phone',
        'department', 'designation', 'joined_date', 'end_date', 'status',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}