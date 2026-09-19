<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','bank_account_id','transaction_id','amount','type','date','description','reference'];
}
