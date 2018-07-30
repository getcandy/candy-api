<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Aggregators;

use Elastica\Aggregation\Min;

class MinPrice
{
    public function getPre()
    {
        $agg = new Min('min_price');
        $agg->setField('min_price');

        return $agg;
    }
}
