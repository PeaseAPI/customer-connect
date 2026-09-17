<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasCompanyScope, HasFiles;

    protected $fillable = ['company_id', 'chat_id', 'user_id', 'message', 'type'];

    public function chat(): BelongsTo { return $this->belongsTo(Chat::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}