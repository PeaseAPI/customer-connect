<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemovalRequestLead extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'lead_id', 'reason', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'status' => ApprovalStatus::class,
    ];

    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
