<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;

class ProductCollectionService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function update($product, array $data)
    {
        $product = $this->getByHashedId($product);
        $collection_ids = app('api')->collections()->getDecodedIds($data['collections']);
        $product->collections()->sync($collection_ids);

        return $product->collections;
    }

    public function delete($productId, $collectionId)
    {
        $product = $this->getByHashedId($productId);
        $collectionId = app('api')->collections()->getDecodedId($collectionId);
        $product->collections()->detach($collectionId);

        return $product->collections;
    }
}
