<?php

namespace GetCandy\Api\Search\Elastic;

use Elastica\Query\BoolQuery;
use Elastica\Query\Nested;
use Elastica\Query\Term;

class CategoryFilter
{
    protected $categories = [];

    public function add($category)
    {
        if (is_iterable($category)) {
            foreach ($category as $cat) {
                $this->categories[] = $cat;
            }
        } else {
            $this->categories[] = $category;
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getFilter()
    {
        $filter = new BoolQuery();

        foreach ($this->categories as $value) {
            $cat = new Nested();
            $cat->setPath('departments');

            $term = new Term();
            $term->setTerm('departments.id', $value);

            $cat->setQuery($term);

            $filter->addMust($cat);
            $this->categories[] = $value;
        }

        return $filter;
    }
}
