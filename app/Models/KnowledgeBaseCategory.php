<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseCategory extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'name', 'description'];
}