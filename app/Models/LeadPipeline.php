<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadPipeline extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'slug', 'priority', 'label_color', 'default',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'default' => 'boolean',
        'priority' => 'integer',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'lead_pipeline_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'lead_pipeline_id');
    }
}
