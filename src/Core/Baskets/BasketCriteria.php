<?php

namespace GetCandy\Api\Core\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;

class BasketCriteria extends AbstractCriteria implements BasketCriteriaInterface
{
    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $basket = new Basket;
        $builder = $basket->with($this->includes ?: []);
        if ($this->id) {
            $builder->where('id', '=', $basket->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
