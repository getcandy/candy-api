<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;

class Category extends AbstractAggregator
{
    /**
     * The categories
     *
     * @var array
     */
    protected $categories = [];

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function getPre()
    {
        $agg = new Terms(str_plural($this->field));
        $agg->setField($this->field . '.filter');

        return $agg;
    }

    public function getPost()
    {
        $nestedAggPost = new Nested(
            'categories_after',
            'departments'
        );

        $agg = new Filter('categories_after_filter');

        // Add boolean
        $postBool = new BoolQuery();

        foreach ($this->categories as $category) {
            $term = new Term;
            $term->setTerm('departments.id', $category);
            $postBool->addMust($term);
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
