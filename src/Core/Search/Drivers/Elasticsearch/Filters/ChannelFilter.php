<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters;

use Elastica\Query\Term;
use Elastica\Query\Range;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;

class ChannelFilter extends AbstractFilter
{
    protected $channel;

    public $handle = 'channel-filter';

    protected $field = 'departments';

    public function process($payload, $type = null)
    {
        $this->channel = $payload;
        return $this;
    }

    public function getQuery()
    {
        $filter = new BoolQuery;

        $cat = new Nested;
        $cat->setPath('channels');

        $term = new Term;
        $term->setTerm('channels.handle', $this->channel->handle);

        $cat->setQuery($term);

        $dateRange = new Nested;
        $dateRange->setPath('channels');

        $range = new Range;
        $range->addField('channels.published_at', [
            'lte' => 'now'
        ]);

        $dateRange->setQuery($range);

        $filter->addMust($dateRange);
        $filter->addMust($cat);

        return $filter;
        $filter = new BoolQuery;

        foreach ($this->value as $value) {
            $filter->addShould(new Match($this->field, $value));
        }

        return $filter;
    }
}
