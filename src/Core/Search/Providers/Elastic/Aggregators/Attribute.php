<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Query\Match;
use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Filter;

class Attribute
{
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

    public function getPre()
    {
        $agg = new Terms(str_plural($this->field));
        $agg->setField($this->field.'.filter');

        return $agg;
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
