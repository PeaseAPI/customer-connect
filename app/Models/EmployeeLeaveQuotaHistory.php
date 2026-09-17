<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveQuotaHistory extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'leave_quota_id', 'action', 'amount', 'reason', 'added_by',
    ];

    public function leaveQuota(): BelongsTo { return $this->belongsTo(EmployeeLeaveQuota::class, 'leave_quota_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}
