<?php

namespace App\Traits;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCompanyScope
{
    /**
     * Boot the has company scope trait for a model.
     */
    protected static function bootHasCompanyScope(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (self $model) {
            if (empty($model->company_id)) {
                $companyId = \Illuminate\Support\Facades\Context::get('current_company_id');
                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
