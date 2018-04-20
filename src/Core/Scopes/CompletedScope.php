<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class CompletedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('completed_at');
    }
}
