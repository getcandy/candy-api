<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Shipping\Models\ShippingExclusion;

trait HasShippingExclusions
{
    public function exclusions()
    {
        return $this->morphMany(ShippingExclusion::class, 'excludable');
    }
}
