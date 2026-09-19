<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','question','answer','faq_category_id','status'];
}
