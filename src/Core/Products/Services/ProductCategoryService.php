<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class ProductCategoryService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function update($product, array $data)
    {
        $product = $this->getByHashedId($product);
        $category_ids = app('api')->categories()->getDecodedIds($data['categories']);
        $product->categories()->sync($category_ids);
        event(new IndexableSavedEvent($product));

        return $product->categories;
    }

    public function delete($productId, $categoryId)
    {
        $product = $this->getByHashedId($productId);
        $categoryId = app('api')->categories()->getDecodedId($categoryId);
        $product->categories()->detach($categoryId);
        event(new IndexableSavedEvent($product));

        return $product->categories;
    }
}
