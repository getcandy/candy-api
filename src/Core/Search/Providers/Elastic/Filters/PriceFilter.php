<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;

class PriceFilter extends AbstractFilter
{
    protected $dirs = [];

    public function process($payload)
    {
        if (empty($payload['values']) || !is_array($payload['values'])) {
            return null;
        }

        foreach ($payload['values'] as $handle => $value) {
            $this->dirs[$handle] = $value;
        }

        return $this;
    }

    /**
     * Add a category into the mix
     *
     * @param string $category
     * @return self
     */
    protected function add($category)
    {
        if (is_iterable($category)) {
            foreach ($category as $cat) {
                $this->categories->push($cat);
            }
        } else {
            $this->categories->push($category);
        }
        return $this;
    }

    /**
     * Get the query for the filter
     *
     * @return mixed
     */
    public function getQuery()
    {
        dd($this->dirs);
        // $filter = new BoolQuery;

        // foreach ($this->categories as $value) {
        //     $cat = new Nested();
        //     $cat->setPath('departments');

        //     $term = new Term;
        //     $term->setTerm('departments.id', $value);

        //     $cat->setQuery($term);

        //     $filter->addMust($cat);
        //     $this->categories[] = $value;
        // }

        // return $filter;
    }
}
