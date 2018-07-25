<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\Range;
use Elastica\Query\BoolQuery;

class PricePointFilter extends AbstractFilter
{
    protected $points = [];

    public function process($payload)
    {
        if (!is_array($payload)) {
            return false;
        }

        foreach ($payload as $key => $value) {
            $this->points[$key] = $value;
        }

        $this->points = collect($this->points);

        return $this;
    }

    /**
     * Get the query for the filter.
     *
     * @return mixed
     */
    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->points as $point) {
            $args = [];

            if (!empty($point['from'])) {
                $args['gte'] = $point['from'];
            }

            if (!empty($point['to'])) {
                $args['lte'] = $point['to'];
            }

            $range = new Range('min_price', $args);

            $filter->addShould($range);
        }

        return $filter;
    }
}
