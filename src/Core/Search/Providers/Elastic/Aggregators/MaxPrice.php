<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Aggregation\Max;

class MaxPrice
{
    public function getPre()
    {
        $agg = new Max('max_price');
        $agg->setField('max_price');

        return $agg;
    }
}
