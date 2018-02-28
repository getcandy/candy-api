<?php
namespace GetCandy\Api\Search\Elastic;

use GetCandy\Api\Search\SearchContract;
use GetCandy\Api\Search\Elastic\Indexer;
use GetCandy\Api\Search\Elastic\Search;

class Elastic implements SearchContract
{
    protected $client;

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
}
