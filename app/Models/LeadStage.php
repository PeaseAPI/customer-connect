<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class LeadStage extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'stage_name', 'priority', 'is_default', 'label_color'];

    protected $casts = ['priority' => 'integer', 'is_default' => 'boolean'];
}
