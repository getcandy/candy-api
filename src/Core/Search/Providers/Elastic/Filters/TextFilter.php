<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Match;
use Elastica\Query\BoolQuery;
use GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\Attribute;

class TextFilter extends AbstractFilter
{
    protected $field;
    protected $value;

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            $match = new Match;
            $match->setFieldAnalyzer($this->field, 'standard');
            $match->setFieldQuery($this->field, $value);
            $filter->addShould($match);
        }

        return $filter;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get an aggregation based on this filter.
     *
     * @return void
     */
    public function aggregate()
    {
        return new Attribute($this->field);
    }

    public function process($payload, $type = null)
    {
        $this->field = $type;
        $this->value = explode(':', $payload);

        return $this;
    }
}
