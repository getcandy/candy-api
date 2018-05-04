<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Status;
use GetCandy\Api\Core\Search\Providers\Elastic\Filters\CategoryFilter;

abstract class AbstractProvider
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $lang = 'en';

    public function __construct(Client $client, CategoryFilter $categoryFilter)
    {
        $this->client = $client;
        $this->categoryFilter = $categoryFilter;
    }



    /**
     * Gets the client for the model.
     * @return Elastica\Client
     */
    public function client()
    {
        if (! $this->client) {
            return new Client();
        }

        return $this->client;
    }

    public function hasIndex($name)
    {
        $elasticaStatus = new Status($this->client());

        return $elasticaStatus->indexExists($name) or $elasticaStatus->aliasExists($name);
    }
}
