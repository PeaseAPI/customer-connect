<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id', 'name', 'color',
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_tags', 'tag_id', 'client_id');
    }
}