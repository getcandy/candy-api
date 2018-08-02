<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;

abstract class AbstractAggregator
{
    /**
     * Get the aggregator.
     *
     * @return mixed
     */
    abstract public function getPre(Search $search, $query);

    /**
     * Get the post aggregation query.
     *
     * @return bool
     */
    public function getPost(Search $search, $query)
    {
        return false;
    }
}
