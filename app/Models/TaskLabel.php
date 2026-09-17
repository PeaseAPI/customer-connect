<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaskLabel extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'label_name',
        'description',
    ];

    public function tasks(): BelongsToMany { return $this->belongsToMany(Task::class, 'task_label_task'); }
}
