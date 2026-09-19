<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class ProjectRating extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','project_id','client_id','rating','comment'];
}
