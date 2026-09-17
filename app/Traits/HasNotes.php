<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    /**
     * Get the notes for the model.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(\App\Models\Note::class, 'noteable');
    }
}
