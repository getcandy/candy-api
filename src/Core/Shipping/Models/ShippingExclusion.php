<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class ShippingExclusion extends BaseModel
{
    public function excludable()
    {
        return $this->morphTo();
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
