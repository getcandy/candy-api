<?php

namespace GetCandy\Api\Core\Products;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;

class ProductCriteria extends AbstractCriteria
{
    /**
     * Query on the sku.
     *
     * @var string
     */
    protected $sku;

    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $product = new Product;
        $builder = $product->channel($this->channel)->with($this->includes ?: []);
        if ($this->sku) {
            $builder->whereHas('variants', function ($q) {
                $q->where('sku', '=', $this->sku);
            });
        }

        if (count($this->ids)) {
            return $builder->whereIn('id', $product->decodeIds($this->ids));
        }

        if ($this->id) {
            $builder->where('id', '=', $product->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
