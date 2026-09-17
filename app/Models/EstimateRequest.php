<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateRequest extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'client_id', 'name', 'email', 'phone',
        'company_name', 'requirement', 'status', 'estimate_id', 'added_by',
    ];

    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function estimate(): BelongsTo { return $this->belongsTo(Estimate::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}
