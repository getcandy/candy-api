<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Search;
use Elastica\Query\Term;
use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;

class Category
{
    /**
     * The categories.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * The filter to apply.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Set the filter on the aggregation.
     *
     * @param mixed $filter
     * @return Attribute
     */
    public function addFilter($filter = null)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function getPre(Search $search, $query)
    {
        // Get our category aggregations
        $nestedAggBefore = new Nested(
            'categories_before',
            'departments'
        );

        $childAgg = new Terms('categories_before_inner');
        $childAgg->setField('departments.id');
        $nestedAggBefore->addAggregation($childAgg);

        return $nestedAggBefore;
    }

    /**
     * Get the post filter.
     *
     * @return void
     */
    public function getPost($value)
    {
        $nestedAggPost = new Nested(
            'categories_post',
            'departments'
        );

        $agg = new Filter('categories_post_filter');

        // Add boolean
        $postBool = new BoolQuery();

        foreach ($value as $category) {
            $term = new Term;
            $term->setTerm('departments.id', $category);
            $postBool->addShould($term);
        }

        // Need to set another agg on categories_remaining
        $childAgg = new Terms('categories_post_inner');
        $childAgg->setField('departments.id');

        // Do the terms in the categories loop...
        $agg->setFilter($postBool);
        $agg->addAggregation($childAgg);

        $nestedAggPost->addAggregation($agg);

        return $nestedAggPost;
    }
}
