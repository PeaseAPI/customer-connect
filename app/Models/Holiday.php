<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = ['company_id', 'holiday_name', 'date', 'category_id', 'description'];

    protected $casts = ['date' => 'date'];
}