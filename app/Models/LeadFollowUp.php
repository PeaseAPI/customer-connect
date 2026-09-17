<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'lead_id', 'follow_up_date', 'follow_up_type',
        'remark', 'next_follow_up', 'status', 'added_by',
    ];

    protected $casts = ['follow_up_date' => 'date', 'next_follow_up' => 'date', 'status' => 'string'];

    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function addedBy(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}
