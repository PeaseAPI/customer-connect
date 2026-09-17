<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    /**
     * Get the custom fields for the model.
     */
    public function customFields(): MorphMany
    {
        return $this->morphMany(\App\Models\CustomFieldValue::class, 'customizable');
    }
}
