<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDetail extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'department_id',
        'designation_id',
        'joining_date',
        'last_date',
        'salary',
        'hourly_rate',
        'address',
        'date_of_birth',
        'gender',
        'reporting_to',
    ];

    protected $casts = [
        'joining_date' => 'date', 'last_date' => 'date',
        'date_of_birth' => 'date',
        'salary' => 'decimal:2', 'hourly_rate' => 'decimal:2', 'skills' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function designation(): BelongsTo { return $this->belongsTo(Designation::class); }
        public function reportingTo(): BelongsTo { return $this->belongsTo(User::class, 'reporting_to'); }
    public function documents(): HasMany { return $this->hasMany(EmployeeDocument::class); }
    public function documentExpiries(): HasMany { return $this->hasMany(EmployeeDocumentExpiry::class, 'employee_document_id'); }
}
