<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLeaveQuota extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'leave_type_id', 'no_of_leaves',
        'leaves_used', 'leaves_remaining', 'cycle', 'added_by',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
    public function histories(): HasMany { return $this->hasMany(EmployeeLeaveQuotaHistory::class, 'leave_quota_id'); }
}
