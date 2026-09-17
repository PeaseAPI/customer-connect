<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalRequest extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id',
        'flow_id',
        'user_id',
        'status',
        'current_step',
        'external_instance_id',
        'form_data',
    ];

    protected $casts = ['status' => ApprovalStatus::class, 'current_step' => 'integer', 'form_data' => 'array'];

    public function flow(): BelongsTo { return $this->belongsTo(ApprovalFlow::class, 'flow_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function approvable(): BelongsTo { return $this->morphTo(); }
    public function records(): HasMany { return $this->hasMany(ApprovalRecord::class, 'request_id'); }
}
