<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Aggregation\Terms;

class Attribute
{
    /**
     * The field to aggregate
     *
     * @var [type]
     */
    protected $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function getQuery()
    {
        $agg = new Terms(str_plural($this->field));
        $agg->setField($this->field . '.filter');

        return $agg;
    }
}