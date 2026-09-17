<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\LeaveDuration;
use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'leave_type_id', 'leave_date',
        'duration', 'reason', 'status', 'approved_by',
    ];

    protected $casts = [
        'leave_date' => 'date', 'duration' => LeaveDuration::class, 'status' => ApprovalStatus::class,
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
