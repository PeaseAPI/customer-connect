<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TicketGroup extends Model
{
    use HasCompanyScope;

    protected $fillable = ['company_id', 'name', 'description', 'is_enabled'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'group_id');
    }
}
