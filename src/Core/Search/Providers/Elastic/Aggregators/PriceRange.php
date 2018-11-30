<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;
use Elastica\Aggregation\Range;

class PriceRange extends AbstractAggregator
{
    /**
     * Get the pre aggregation.
     *
     * @param Search $search
     * @param Query $query
     * @return Query
     */
    public function getPre(Search $search = null, $query = null, $postFilter = null)
    {
        // Add max price aggregator
        $max = new MaxPrice;

        $boolQuery = $query->getQuery();

        if ($postFilter) {
            foreach ($postFilter->getParam('filter') as $filter) {
                $boolQuery->addFilter($filter);
            }
        }

        $query->setQuery($boolQuery);
        $query->addAggregation($max->getPre());

        $results = $search->setQuery($query)->search();

        $max = floor($results->getAggregation('max_price')['value']);

        // Get the config range filter.
        $definedRanges = collect(config('getcandy.search.aggregation.price.ranges', []));

        $ranges = $definedRanges->first(function ($range) use ($max) {
            if ($max < last($range)) {
                return $range;
            }

            return false;
        });

        $ranges = $ranges ?: $definedRanges->last();

        $rangeQuery = new Range('price');
        $rangeQuery->setField('min_price');

        // Add our first range as being zero to the first one.
        $rangeQuery->addRange(0, array_first($ranges) - 1);
        foreach ($ranges as $index => $range) {
            $next = $ranges[$index + 1] ?? null;
            $rangeQuery->addRange($range, $next ? $next - 1 : null);
        }

        return $rangeQuery;
    }
}
