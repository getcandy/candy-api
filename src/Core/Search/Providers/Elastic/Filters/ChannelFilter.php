<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\Term;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;

class ChannelFilter extends AbstractFilter
{
    protected $channel;

    public $handle = 'channel-filter';

    public function process($payload, $type = null)
    {
        $this->channel = $payload;
    }

    public function getQuery()
    {
        $filter = new BoolQuery;

        $cat = new Nested;
        $cat->setPath('channels');

        $term = new Term;
        $term->setTerm('channels.handle', $this->channel);

        $cat->setQuery($term);

        $filter->addMust($cat);

        return $filter;
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            $filter->addShould(new Match($this->field, $value));
        }

        return $filter;
    }
}
