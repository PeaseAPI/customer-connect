<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','knowledge_base_id','filename','original_name','mime_type','size'];
}
