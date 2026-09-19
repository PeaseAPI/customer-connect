<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SeoDetail extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','page_title','meta_description','meta_keywords','og_title','og_description','og_image','canonical_url'];
}
