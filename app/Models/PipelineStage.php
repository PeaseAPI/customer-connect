<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'lead_pipeline_id', 'name', 'slug', 'type', 'priority',
        'label_color', 'default', 'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'default' => 'boolean',
        'priority' => 'integer',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(LeadPipeline::class, 'lead_pipeline_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'pipeline_stage_id');
    }
}
