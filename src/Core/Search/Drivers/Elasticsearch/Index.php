<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch;

use Elastica\Index as ElasticIndex;

class Index
{
    public $exists;

    public $actual;

    public $language;

    public function __construct(ElasticIndex $index, $language = 'en')
    {
        $this->exists = $index->exists();
        $this->actual = $index;
        $this->language = $language;
    }
}
