<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientNote extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'client_id', 'title', 'type', 'member_id',
        'is_client_show', 'ask_password', 'details', 'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'type' => 'boolean',
        'is_client_show' => 'boolean',
        'ask_password' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
