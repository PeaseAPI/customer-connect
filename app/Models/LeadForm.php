<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class LeadForm extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','name','fields','is_active','redirect_url','success_message'];
}
