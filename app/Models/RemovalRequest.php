<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemovalRequest extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'user_id', 'reason', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'status' => ApprovalStatus::class,
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
