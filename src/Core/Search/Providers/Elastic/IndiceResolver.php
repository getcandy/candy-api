<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\CategoryType;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;

class IndiceResolver
{
    /**
     * @var array
     */
    protected $types = [
        Product::class => ProductType::class,
        Category::class => CategoryType::class,
    ];

    /**
     * Get the document type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \GetCandy\Api\Core\Search\Providers\Elastic\Types
     */
    public function getType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (! $this->hasType($model)) {
            abort(400, "No type available for {$model}");
        }

        return new $this->types[$model];
    }

    /**
     * Checks whether an indexer exists.
     * @param  string|\Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function hasType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return isset($this->types[$model]);
    }
}
