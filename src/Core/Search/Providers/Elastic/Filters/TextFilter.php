<?php
namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;

class TextFilter extends AbstractFilter
{
    protected $field;
    protected $value;

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            $filter->addShould(new Match($this->field, $value));
        }

        return $filter;
    }

    public function process($payload, $type = null)
    {
        $this->field = $type;
        $this->value = explode(':', $payload);
        return $this;
    }
}
