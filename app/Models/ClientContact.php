<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'client_id', 'contact_name', 'title', 'email',
        'phone', 'mobile', 'skype', 'linkedin', 'address',
        'is_primary', 'added_by', 'last_updated_by',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
