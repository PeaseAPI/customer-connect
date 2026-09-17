<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Context;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = Context::get('current_company_id');

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }

    /**
     * Extend the query builder with macros.
     */
    public function extend(Builder $builder): void
    {
        $this->addWithoutCompany($builder);
        $this->addWithAllCompanies($builder);
    }

    /**
     * Add the withoutCompany macro.
     */
    protected function addWithoutCompany(Builder $builder): void
    {
        $builder->macro('withoutCompany', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the withAllCompanies macro.
     */
    protected function addWithAllCompanies(Builder $builder): void
    {
        $builder->macro('withAllCompanies', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
