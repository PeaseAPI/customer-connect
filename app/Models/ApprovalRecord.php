<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRecord extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'request_id', 'approver_id', 'step',
        'action', 'remark', 'external_action', 'acted_at',
    ];

    protected $casts = ['step' => 'integer', 'external_action' => 'boolean', 'acted_at' => 'datetime'];

    public function request(): BelongsTo { return $this->belongsTo(ApprovalRequest::class, 'request_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_id'); }
}
