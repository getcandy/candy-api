<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ShippingZone extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
