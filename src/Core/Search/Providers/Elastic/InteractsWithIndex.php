<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Status;
use Elastica\Type\Mapping;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\CategoryType;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;

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

    protected $lang = 'en';

    /**
     * Gets the base index name.
     *
     * @return string
     */
    protected function getBaseIndexName()
    {
        return config('getcandy.search.index_prefix').'_'.$this->type->getHandle();
    }

    public function against($types)
    {
        $this->type = $this->getType($types);

        return $this;
    }

    /**
     * Gets the name of the default index.
     *
     * @return string
     */
    protected function getDefaultIndex()
    {
        $defaultLang = app('api')->languages()->getDefaultRecord();

        return $this->getBaseIndexName()."_{$defaultLang->lang}";
    }

    /**
     * Get the type for a model.
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
        if (! $this->hasType($model)) {
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
     * Determines if the index exists in elastic.
     *
     * @param string $name
     * @return bool
     */
    public function hasIndex($name)
    {
        $elasticaStatus = new Status($this->client);

        return $elasticaStatus->indexExists($name) || $elasticaStatus->aliasExists($name);
    }

    /**
     * Get the suffix of the current index.
     *
     * @param string $name
     * @return string
     */
    protected function getCurrentIndexSuffix($name)
    {
        return $this->getNextIndexSuffix($name) == 'a' ? 'b' : 'a';
    }

    /**
     * Updates the mappings for the model.
     * @param  Elastica\Index $index
     * @return void
     */
    public function updateMappings($index)
    {
        $elasticaType = $index->getType($this->type->getHandle());

        $mapping = new Mapping();
        $mapping->setType($elasticaType);

        $mapping->setProperties($this->type->getMapping());
        $mapping->send();
    }

    /**
     * Get the next suffix.
     *
     * @param string $name
     * @return string
     */
    protected function getNextIndexSuffix($name)
    {
        // Somethings gone wrong and hasn't cleaned up...
        if ($this->hasIndex($name.'_a') && $this->hasIndex($name.'_b')) {
            $this->client->getIndex($name.'_b')->delete();
        }

        if ($this->hasIndex($name.'_a')) {
            return 'b';
        }

        return 'a';
    }

    /**
     * Get the search index.
     *
     * @return string
     */
    public function getSearchIndex()
    {
        return $this->type->getIndexName().'_'.$this->lang;
    }

    public function getCurrentIndex()
    {
        return $this->client->getIndex(
            $this->getSearchIndex()
        );
    }
}
