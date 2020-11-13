<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Aggregators;

use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\Search;

class Category extends AbstractAggregator
{
    /**
     * The categories.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * @var string
     */
    protected $field = 'departments';

    /**
     * The filter to apply.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Set the filter on the aggregation.
     *
     * @param  mixed  $filter
     * @return $this
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

    public function get($filters)
    {
        $nestedAgg = new Nested(
            'categories',
            'departments'
        );

        $childAgg = new Terms('categories');
        $childAgg->setField('departments.id');
        // $nestedAgg->addAggregation($childAgg);

        // $postBool = new BoolQuery();

        // // dd($this->filters);
        // foreach ($this->filters as $filter) {
        //     $postBool->addMust($filter['filter']->getQuery());
        // }

        // $filterAgg->setFilter($postBool);
        $nestedAgg->addAggregation($childAgg);
        // $nestedAgg->setFilter($postBool);
        // $nestedAgg->addAggregation($agg);

        // $agg = new Filter('categories');

        // // Add boolean
        // $postBool = new BoolQuery();

        // // foreach ($this->filters as $filter) {
        // //     dd($filter['filter']->getQuery());
        // //     $postBool->addMust($filter['filter']->getQuery());
        // // }

        // // Need to set another agg on categories_remaining
        // $childAgg = new Terms('categories_post_inner');
        // $childAgg->setField('departments.id');

        // // Do the terms in the categories loop...
        // $agg->setFilter($postBool);
        // $agg->addAggregation($childAgg);

        // $nestedAggPost->addAggregation($agg);

        return $nestedAgg;
    }

    /**
     * Get the post filter.
     *
     * @param  array  $value
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
