<?php

namespace GetCandy\Api\Core\Products\Criteria;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;

class ProductFamilyCriteria extends AbstractCriteria
{
    public function getBuilder()
    {
        $family = new ProductFamily;
        $builder = $family->with($this->includes ?: []);
        if ($this->id) {
            $builder->where('id', '=', $family->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
