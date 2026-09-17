<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'source_name'];
}
