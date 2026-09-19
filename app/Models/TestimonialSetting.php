<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TestimonialSetting extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','name','designation','company_name','testimonial','image'];
}
