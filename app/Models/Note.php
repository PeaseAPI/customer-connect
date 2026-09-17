<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'user_id', 'noteable_type', 'noteable_id',
        'title', 'body', 'is_encrypted',
    ];

    protected $casts = ['is_encrypted' => 'boolean', 'body' => 'encrypted'];

    public function noteable(): MorphTo { return $this->morphTo(); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}