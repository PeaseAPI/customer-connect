<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentExpiry extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'employee_document_id', 'expiry_date', 'notified',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'notified' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocument::class, 'employee_document_id');
    }
}
