<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;

abstract class AbstractAggregator
{
    /**
     * Get the aggregator
     *
     * @return mixed
     */
    abstract public function getQuery(Search $search, $query);
}
