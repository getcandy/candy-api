<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Query\Match;
use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;

class Attribute
{
    /**
     * The filter to apply.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The field to aggregate.
     *
     * @var [type]
     */
    protected $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

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

    public function addFilters(iterable $filters)
    {
        if (is_array($filters)) {
            $filters = collect($filters);
        }
        $filters->filter(function ($f) {
            return $this->field != $f['filter']->getField();
        })->each(function ($f) {
            $this->addFilter($f);
        });

        return $this;
    }

    public function get($filters)
    {
        $filterAgg = new Filter($this->field);

        $size = (int) config('getcandy.search.aggregation.attribute.size', 10);
        $agg = new Terms($this->field);
        $agg->setField($this->field.'.filter');
        $agg->setSize($size);

        $postBool = new BoolQuery();

        foreach ($this->filters as $filter) {
            $postBool->addMust($filter['filter']->getQuery());
        }

        $filterAgg->setFilter($postBool);
        $filterAgg->addAggregation($agg);

        return $filterAgg;
    }

    public function getPre()
    {
        $size = (int) config('getcandy.search.aggregation.attribute.size', 10);

        if (empty($this->filters)) {
            $agg = new Terms($this->field);
            $agg->setField($this->field.'.filter');
            $agg->setSize($size);

            return $agg;
        }

        $filterAgg = new Filter($this->field);

        $agg = new Terms($this->field);
        $agg->setField($this->field.'.filter');
        $agg->setOrder('_term', 'asc');
        $agg->setSize($size);
        $postBool = new BoolQuery();

        foreach ($this->filters as $filter) {
            $postBool->addMust($filter['filter']->getQuery());
        }

        $filterAgg->setFilter($postBool);
        $filterAgg->addAggregation($agg);

        return $filterAgg;
    }

    public function getPost($value)
    {
        $agg = new Filter($this->field.'_after');

        if (! is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $value) {
            $postBool = new BoolQuery();
            $match = new Match;
            $match->setFieldAnalyzer($this->field, 'standard');
            $match->setFieldQuery($this->field, $value);
            $postBool->addShould($match);
        }

        $agg->setFilter($postBool);
        $agg->addAggregation($this->getPre());

        return $agg;
    }
}
