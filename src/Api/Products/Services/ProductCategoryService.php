<?php

namespace GetCandy\Api\Products\Services;

use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Products\Models\ProductFamily;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Search\Events\IndexableSavedEvent;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Search\Algolia\Indexable;

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
