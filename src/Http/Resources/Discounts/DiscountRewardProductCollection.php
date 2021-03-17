<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractCollection;

class DiscountRewardProductCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = DiscountRewardProductResource::class;
}
