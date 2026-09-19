<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class ChatMention extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','chat_message_id','user_id'];
}
