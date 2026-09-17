<?php

namespace App\Models;

use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id', 'client_id', 'lead_name', 'lead_email', 'lead_mobile',
        'lead_address', 'agent_id', 'source_id', 'status_id', 'pipeline_stage_id',
        'next_follow_up', 'value', 'is_client', 'client_converted_id', 'created_by',
    ];

    protected $casts = [
        'next_follow_up' => 'date', 'value' => 'decimal:2', 'is_client' => 'boolean',
    ];

    public function agent(): BelongsTo { return $this->belongsTo(User::class, 'agent_id'); }
    public function source(): BelongsTo { return $this->belongsTo(LeadSource::class, 'source_id'); }
    public function status(): BelongsTo { return $this->belongsTo(LeadStage::class, 'status_id'); }
    public function pipelineStage(): BelongsTo { return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id'); }
    public function contacts(): HasMany { return $this->hasMany(LeadContact::class); }
    public function followUps(): HasMany { return $this->hasMany(LeadFollowUp::class); }
    public function convertedClient(): BelongsTo { return $this->belongsTo(User::class, 'client_converted_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
