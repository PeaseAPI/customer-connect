<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionReply extends Model
{
    use HasCompanyScope, SoftDeletes;

    protected $fillable = [
        'company_id', 'discussion_id', 'user_id', 'body', 'is_solution',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'is_solution' => 'boolean',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
