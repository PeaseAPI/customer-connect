<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','lead_id','user_id','note'];
}
