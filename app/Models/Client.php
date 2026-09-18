<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Client extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'name', 'industry', 'contact_name', 'contact_phone',
        'contact_email', 'address', 'owner_id', 'level_id', 'source_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(ClientCategory::class, 'level_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'client_tags', 'client_id', 'tag_id')->withTimestamps();
    }
}