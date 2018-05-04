<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Status;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\CategoryType;

trait InteractsWithIndex
{
    /**
     * @var array
     */
    protected $types = [
        Product::class => ProductType::class,
        Category::class => CategoryType::class,
    ];

    protected $type = null;

    protected $suffix = null;

    protected $client;

    /**
     * Gets the base index name
     *
     * @return string
     */
    protected function getBaseIndexName()
    {
        return config('getcandy.search.index_prefix') . '_' . $this->type->getHandle();
    }

    public function against($types)
    {
        $this->type = $this->getType($types);
        return $this;
    }

    /**
     * Gets the name of the default index
     *
     * @return string
     */
    protected function getDefaultIndex()
    {
        $defaultLang = app('api')->languages()->getDefaultRecord();
        return $this->getBaseIndexName() . "_{$defaultLang->lang}";
    }

    /**
     * Get the type for a model
     *
     * @param Model|string $model
     * @throws Symfony\Component\HttpKernel\Exception\HttpException;
     * @return mixed
     */
    public function getType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (!$this->hasType($model)) {
            abort(400, "No type available for {$model}");
        }
        return new $this->types[$model];
    }

    /**
     * Checks whether an indexer exists.
     * @param  mixed  $model
     * @return bool
     */
    public function hasType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        return isset($this->types[$model]);
    }

    /**
     * Determines if the index exists in elastic
     *
     * @param string $name
     * @return boolean
     */
    public function hasIndex($name)
    {
        $elasticaStatus = new Status($this->client);
        return $elasticaStatus->indexExists($name) || $elasticaStatus->aliasExists($name);
    }

    /**
     * Get the suffix of the current index
     *
     * @param string $name
     * @return string
     */
    protected function getCurrentIndexSuffix($name)
    {
        return $this->getNextIndexSuffix($name) == 'a' ? 'b' : 'a';
    }

    /**
     * Get the next suffix
     *
     * @param string $name
     * @return string
     */
    protected function getNextIndexSuffix($name)
    {
        if ($this->hasIndex($name . '_a')) {
            return 'b';
        }
        return 'a';
    }
}
