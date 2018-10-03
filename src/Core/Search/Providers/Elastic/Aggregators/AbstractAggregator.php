<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;

abstract class AbstractAggregator
{
    /**
     * The filter to apply.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Get the aggregator.
     *
     * @return mixed
     */
    abstract public function getPre(Search $search = null, $query = null, $postFilter = null);

    /**
     * Get the post aggregation query.
     *
     * @return bool
     */
    public function getPost($value)
    {
        return false;
    }

    /**
     * Set the filter on the aggregation.
     *
     * @param mixed $filter
     * @return Attribute
     */
    public function addFilter($filter = null)
    {
        $this->filters[] = $filter;

        return $this;
    }
}
