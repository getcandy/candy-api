<?php
namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\BoolQuery;

class TextFilter extends AbstractFilter
{
    protected $field;
    protected $value;

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            $term = new Term;
            $term->setTerm($this->field, $value);
            $filter->addShould($term);
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
