<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class DiscussionCategory extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'name'];
}