<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientCategory extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = ['company_id', 'category_name'];

    public function subCategories(): HasMany
    {
        return $this->hasMany(ClientSubCategory::class, 'category_id');
    }
}
