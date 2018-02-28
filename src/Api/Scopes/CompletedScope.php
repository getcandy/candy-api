<?php

namespace GetCandy\Api\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompletedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('completed_at');
    }
}
