<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class InvoiceFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','invoice_id','user_id','filename','original_name','mime_type','size'];
}
