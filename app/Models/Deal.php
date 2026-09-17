<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasFactory;

    protected $fillable = [
        'company_id', 'pipeline_stage_id', 'client_id', 'currency_id',
        'agent_id', 'source_id', 'category_id', 'column_priority',
        'company_name', 'deal_name', 'website', 'address', 'salutation',
        'client_name', 'client_email', 'mobile', 'cell', 'office',
        'city', 'state', 'country', 'postal_code', 'note',
        'next_follow_up', 'value', 'total_value',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'next_follow_up' => 'datetime',
        'value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'column_priority' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DealNote::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DealHistory::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
