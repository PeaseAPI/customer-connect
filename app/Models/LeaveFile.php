<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class LeaveFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','leave_id','user_id','filename','original_name','mime_type','size'];
}
