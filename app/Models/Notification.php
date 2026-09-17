<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasCompanyScope, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id', 'user_id', 'type', 'title', 'message',
        'data', 'read_at', 'channel',
    ];

    protected $casts = ['data' => 'array', 'read_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function scopeUnread($query) { return $query->whereNull('read_at'); }
}