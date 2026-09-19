<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TicketChannel extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','name','is_default'];
}
