<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Search\SearchContract;

class Elastic implements SearchContract
{
    protected $client;

    protected $indexer;

    public function __construct(Indexer $indexer)
    {
        // $this->client = $client;
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
