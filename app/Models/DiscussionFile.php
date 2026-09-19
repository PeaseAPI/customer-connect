<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class DiscussionFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','discussion_id','filename','original_name','mime_type','size'];
}
