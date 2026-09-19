<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class ChatMessageFile extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','chat_message_id','filename','original_name','mime_type','size'];
}
