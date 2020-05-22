<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompletedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('completed_at');
    }
}
