<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','copyright_text','footer_note'];
}
