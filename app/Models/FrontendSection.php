<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class FrontendSection extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','title','subtitle','description','page_section','type','is_active','sort_order'];
}
