<?php

namespace GetCandy\Api\Search\Algolia;

use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Search\Algolia\Indexers\ProductIndexer;
use GetCandy\Api\Search\SearchContract;
use Illuminate\Database\Eloquent\Model;
use AlgoliaSearch\Client;

class Algolia implements SearchContract
{
    protected $indexes = [

    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $indexer;

    /**
     * @var array
     */
    protected $indexers = [
        Product::class => ProductIndexer::class,
    ];

    public function __construct()
    {
        $this->client = new Client(config('search.algolia.app_id'), config('search.algolia.app_key'));
    }

    public function against($types)
    {
        $this->indexer = $this->getIndexer($types);
        return $this;
    }
    /**
     * Checks whether an indexer exists
     * @param  mixed  $model
     * @return boolean
     */
    public function hasIndexer($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        return isset($this->indexers[$model]);
    }

    /**
     * Adds a model to the index
     * @param  Model  $model
     * @return boolean
     */
    public function indexObject(Model $model)
    {
        // Get the indexer.
        $indexer = $this->getIndexer($model);
        $indexables = $indexer->getIndexables($model);

        foreach ($indexables as $indexable) {
            $index = $this->client->initIndex($indexable->getIndex());
            $index->addObject($indexable->getData());
        }

        return true;
    }

    /**
     * List indexes available
     * @return array
     */
    public function getIndexes()
    {
        return $this->client->listIndexes();
    }

    /**
     * Updates the mappings for the model
     * @param  Elastica\Index $index
     * @return void
     */
    public function updateMappings()
    {
        //
    }

    /**
     * Create an index based on the model
     * @return void
     */
    public function createIndex()
    {
        //
    }

    /**
     * Gets the client for the model
     * @return Elastica\Client
     */
    public function client()
    {
        //
    }

    /**
     * Returns the index for the model
     * @return Elastica\Index
     */
    public function getIndex($type, $lang = 'en')
    {
        $index = config('search.index_prefix') . '_' . $type . '_' . $lang;
        return $this->client->initIndex($index);
    }

    public function with($searchterm)
    {
        return $this->search($searchterm);
    }

    /**
     * Searches the index
     * @param  string $keywords
     * @return array
     */
    public function search($keywords)
    {
        if (!$this->indexer) {
            abort(400, 'You need to set an indexer first');
        }

        $index = $this->getIndex($this->indexer->type);
        return $index->search($keywords);
    }

    /**
     * Gets the indexer for a model
     * @param  mixed $model
     * @return mixed
     */
    protected function getIndexer($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (!$this->hasIndexer($model)) {
            abort(400, "No indexer available for {$model}");
        }
        return new $this->indexers[$model];
    }
}
