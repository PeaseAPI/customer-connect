<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class ProposalTemplate extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','name','subject','body','variables'];
}
