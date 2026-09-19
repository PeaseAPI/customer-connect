<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TicketReplyTemplate extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','name','body'];
}
