<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TicketSetting extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','auto_assign','notify_customer','notify_agent','default_status'];
}
