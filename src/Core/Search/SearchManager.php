<?php

namespace GetCandy\Api\Core\Search;

use GetCandy\Api\Core\Search\Contracts\SearchManagerContract;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Elasticsearch;
use Illuminate\Support\Manager;

class SearchManager extends Manager implements SearchManagerContract
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create the PayPal driver.
     *
     * @return \GetCandy\Api\Core\Search\Elasticsearch\Elasticsearch
     */
    public function createElasticsearchDriver()
    {
        return $this->buildProvider(
            Elasticsearch::class
        );
    }

    /**
     * Build a layout provider instance.
     *
     * @param  string  $provider
     * @return \GetCandy\Api\Core\Payments\Providers\AbstractProvider
     */
    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver()
    {
        return config('getcandy.search.driver', 'elasticsearch');
    }
}
