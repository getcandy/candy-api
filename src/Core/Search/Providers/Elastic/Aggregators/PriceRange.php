<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;
use Elastica\Aggregation\Range;

class PriceRange extends AbstractAggregator
{
    /**
     * Get the pre aggregation
     *
     * @param Search $search
     * @param Query $query
     * @return Query
     */
    public function getPre(Search $search, $query)
    {
        // Add max price aggregator
        $max = new MaxPrice;

        $query->addAggregation($max->getPre());

        $results = $search->setQuery($query)->search();

        $max = floor($results->getAggregation('max_price')['value']);

        $ranges = range(0, $max, $max / 5);

        // Clean them up!
        // Do this first so we have a nice array to work with on the next iteration.
        foreach ($ranges as $index => $range) {
            $ranges[$index] = round($range, -1);
        }

        $rangeQuery = new Range('price_points');
        $rangeQuery->setField('min_price');

        // Go again, this time building up our agg
        foreach ($ranges as $index => $range) {
            $next = $ranges[$index + 1] ?? null;

            $rangeQuery->addRange($range, $next ? $next - 1 : null);
        }

        return $rangeQuery;
    }
}
