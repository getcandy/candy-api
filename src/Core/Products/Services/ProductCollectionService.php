<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;

class ProductCollectionService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function update($product, array $data)
    {
        $product = $this->getByHashedId($product);
        $collection_ids = GetCandy::collections()->getDecodedIds($data['collections']);
        $product->collections()->sync($collection_ids);

        return $product->collections;
    }

    public function delete($productId, $collectionId)
    {
        $product = $this->getByHashedId($productId);
        $collectionId = GetCandy::collections()->getDecodedId($collectionId);
        $product->collections()->detach($collectionId);

        return $product->collections;
    }
}
