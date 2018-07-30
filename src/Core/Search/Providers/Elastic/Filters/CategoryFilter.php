<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;

class CategoryFilter extends AbstractFilter
{
    protected $categories = [];

    public function __construct()
    {
        $this->categories = collect();
    }

    public function process($payload, $type = null)
    {
        if (! empty($payload['values']) && is_array($payload['values'])) {
            $this->add($payload['values']);
        } elseif (is_string($payload)) {
            $this->add($payload);
        } else {
            $this->add($payload->encodedId());
        }

        return $this;
    }

    /**
     * Add a category into the mix.
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
     * Get the query for the filter.
     *
     * @return mixed
     */
    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->categories as $value) {
            $cat = new Nested();
            $cat->setPath('departments');

            $term = new Term;
            $term->setTerm('departments.id', $value);

            $cat->setQuery($term);


            $filter->addMust($cat);
        }

        return $filter;
    }
}
