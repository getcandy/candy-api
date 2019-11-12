<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ShippingRegion extends BaseModel
{
    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
