<?php

namespace GetCandy\Api\Core\Collections\Criteria;

use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use GetCandy\Api\Core\Collections\Models\Collection;

class CollectionCriteria extends AbstractCriteria
{
    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $collection = new Collection;

        $builder = $collection->with($this->includes ?: []);

        if ($this->id) {
            $builder->where('id', '=', $collection->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
