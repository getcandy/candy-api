<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;
use GetCandy\Api\Core\Search\SearchContract;

class Elastic implements SearchContract
{
    /**
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\Search
     */
    protected $client;

    /**
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\Indexer
     */
    protected $indexer;

    public function __construct(Search $client, Indexer $indexer)
    {
        $this->client = $client;
        $this->indexer = $indexer;
    }

    public function indexer()
    {
        return $this->indexer;
    }

    public function client()
    {
        return $this->client;
    }

    public function products()
    {
        return app()->make(ProductType::class);
    }

    public function parseAggregations(array $aggregations)
    {
        return (new AggregationResolver)->resolve(
            $aggregations
        );
    }
}
