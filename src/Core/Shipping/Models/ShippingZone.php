<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Countries\Models\Country;

class ShippingZone extends BaseModel
{
    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = [
        'name',
    ];

    public function methods()
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_zones');
    }

    public function prices()
    {
        return $this->hasMany(ShippingPrice::class);
    }

    public function regions()
    {
        return $this->hasMany(ShippingRegion::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'shipping_zone_country');
    }
}
