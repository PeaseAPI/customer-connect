<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class InvoiceReminderHistory extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','invoice_id','reminded_at','reminder_type'];
}
