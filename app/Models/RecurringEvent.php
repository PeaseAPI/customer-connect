<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class RecurringEvent extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','event_id','frequency','repeat_until','custom_interval'];
}
