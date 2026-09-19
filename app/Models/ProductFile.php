<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','product_id','filename','original_name','mime_type','size'];
}
