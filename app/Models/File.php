<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'user_id', 'filename', 'hashname', 'size',
        'fileable_type', 'fileable_id', 'disk', 'path',
    ];

    protected $casts = ['size' => 'integer'];

    public function fileable(): MorphTo { return $this->morphTo(); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}