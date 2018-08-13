<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;
use GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\Category;

class CategoryFilter extends AbstractFilter
{
    protected $categories = [];

    public $handle = 'category-filter';

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
        foreach (explode(':', $category) as $cat) {
            $this->categories->push($cat);
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

    public function getValue()
    {
        return $this->categories;
    }

    /**
     * Get an aggregation based on this filter.
     *
     * @return void
     */
    public function aggregate()
    {
        return new Category('category');
    }
}
