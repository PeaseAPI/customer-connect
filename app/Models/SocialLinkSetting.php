<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SocialLinkSetting extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','platform','url','is_active'];
}
