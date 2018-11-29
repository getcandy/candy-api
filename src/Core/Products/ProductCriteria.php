<?php

namespace GetCandy\Api\Core\Products;

use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use GetCandy\Api\Core\Products\Models\Product;

class ProductCriteria extends AbstractCriteria
{
    /**
     * Query on the sku
     *
     * @var string
     */
    protected $sku;


    /**
     * Gets the underlying builder for the query
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $product = new Product;
        $builder = $product->with($this->includes ?: []);
        if ($this->sku) {
            $builder->whereHas('variants', function ($q) {
                $q->where('sku', '=', $this->sku);
            });
        }
        if ($this->id) {
            $builder->where('id', '=', $product->decodeId($this->id));
            return $builder;
        }
        return $builder;
    }
}