<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasCompanyScope, HasFiles, HasFactory;

    protected $fillable = [
        'company_id', 'employee_detail_id', 'document_name',
        'document_type', 'expiry_date',
    ];

    protected $casts = ['expiry_date' => 'date'];

    public function employeeDetail(): BelongsTo { return $this->belongsTo(EmployeeDetail::class); }
}