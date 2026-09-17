<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardIcon extends Model
{
    protected $fillable = ['name', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}