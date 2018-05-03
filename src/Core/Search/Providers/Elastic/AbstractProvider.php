<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Status;

abstract class AbstractProvider
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var CategoryFilter
     */
    protected $categoryFilter;

    /**
     * @var string
     */
    protected $lang = 'en';

    public function __construct(Client $client, CategoryFilter $categoryFilter)
    {
        $this->client = $client;
        $this->categoryFilter = $categoryFilter;
    }

    public function language($lang = 'en')
    {
        $this->lang = $lang;

        return $this;
    }

    public function against($types)
    {
        $this->indexer = $this->getIndexer($types);

        return $this;
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

    /**
     * Gets the indexer for a model.
     * @param  mixed $model
     * @return mixed
     */
    public function getIndexer($model)
    {

    }
}
