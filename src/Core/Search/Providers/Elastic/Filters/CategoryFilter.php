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
        foreach ($payload as $category) {
            if (! empty($category['values']) && is_array($category['values'])) {
                $this->add($category['values']);
            } elseif (is_string($category)) {
                $this->add($category);
            } else {
                $this->add($category->encodedId());
            }
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
        $this->categories->push($category);

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

            if ($this->categories->count() > 1) {
                $filter->addShould($cat);
            } else {
                $filter->addMust($cat);
            }
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
