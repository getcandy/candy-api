<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
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
        $categoryIds = GetCandy::categories()->getDecodedIds($data['categories']);

        $categories = collect($categoryIds)->mapWithKeys(function ($id, $index) {
            return [$id => ['position' => $index + 1]];
        });

        $product->categories()->sync($categories);
        event(new IndexableSavedEvent($product));

        return $product->categories;
    }

    public function attach($category, array $products)
    {
        $category = GetCandy::categories()->getByHashedId($category);

        $id = $this->getDecodedIds($products);

        $category->products()->attach($id);

        foreach ($this->getByHashedIds($products) as $product) {
            IndexObjects::run([
                'documents' => $product,
            ]);
        }

        return $category;
    }

    public function delete($productId, $categoryId)
    {
        $product = $this->getByHashedId($productId);
        $categoryId = GetCandy::categories()->getDecodedId($categoryId);
        $product->categories()->detach($categoryId);
        event(new IndexableSavedEvent($product));

        return $product->categories;
    }
}
