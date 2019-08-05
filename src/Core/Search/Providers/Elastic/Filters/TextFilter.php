<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Match;
use Elastica\Query\Range;
use Elastica\Query\BoolQuery;
use GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\Attribute;

class TextFilter extends AbstractFilter
{
    public $handle = 'text-filter';
    protected $field;
    protected $value;

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            if (strpos($value, '-') && preg_match('/^[0-9-*]+$/', $value)) {
                $value = explode('-', $value);
            }
            if (is_array($value)) {
                $range = new Range($this->field . '.filter', [
                    'gte' => (int) $value[0],
                    'lte' => $value[1] == '*' ? null : (int) $value[1],
                ]);
                $filter->addShould($range);
            } else {
                $match = new Match;
                $match->setFieldAnalyzer($this->field . '.filter', 'keyword');
                $match->setFieldQuery($this->field . '.filter', $value);

                $filter->addShould($match);
            }
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
