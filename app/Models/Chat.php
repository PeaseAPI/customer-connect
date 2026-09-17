<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = ['company_id', 'type', 'name', 'created_by'];

    public function participants(): BelongsToMany {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot('last_read_message_id')->withTimestamps();
    }
    public function messages(): HasMany { return $this->hasMany(ChatMessage::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}