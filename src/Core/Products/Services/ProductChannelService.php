<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;

class ProductChannelService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product;
    }

    /**
     * Stores a product association.
     *
     * @param  string  $product
     * @param  array  $data
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function store($product, $channels)
    {
        $product = $this->getByHashedId($product);
        $product->channels()->sync(
            $this->getChannelMapping($channels)
        );
        $product->load('channels');

        return $product;
    }

    /**
     * Destroys product customer groups.
     *
     * @param  string  $product
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function destroy($product)
    {
        $product = $this->getByHashedId($product);
        $product->customerGroups()->detach();

        return $product;
    }
}
