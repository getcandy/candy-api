<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderScope extends AbstractScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = $this->getUser();

        // if ($model->user_id && $user && ($user->id == $model->user_id)) {
        //     return $builder;
        // }

        // $this->resolve(function () use ($builder) {
        //     $builder->whereNull('placed_at')
        //         ->where('status', '!=', 'expired');
        // });
    }
}
