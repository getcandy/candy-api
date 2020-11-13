<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Aggregators;

abstract class AbstractAggregator
{
    /**
     * The filter to apply.
     *
     * @var array
     */
    protected $filters = [];

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
     * @param  mixed  $filter
     * @return $this
     */
    public function addFilter($filter = null)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function addFilters(iterable $filters)
    {
        if (is_array($filters)) {
            $filters = collect($filters);
        }
        $filters->filter(function ($f) {
            return $this->field != $f['filter']->getField();
        })->each(function ($f) {
            $this->addFilter($f);
        });

        return $this;
    }
}
