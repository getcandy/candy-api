<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Range;
use Elastica\Query\BoolQuery;

class PricePointFilter extends AbstractFilter
{
    protected $points = [];
    protected $seperator = ':';
    protected $delimiter = '-';
    public $handle = 'price-point-filter';

    public function process($payload, $type = null)
    {
        $pricePoints = explode($this->seperator, $payload);

        foreach ($pricePoints as $point) {
            $this->points[] = explode($this->delimiter, $point);
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

            $args['gte'] = $point[0];

            // If we have a single array, just do LTE
            if (isset($point[1])) {
                $args['lte'] = $point[1];
            }

            $range = new Range('min_price', $args);

            $filter->addShould($range);
        }

        return $filter;
    }
}
