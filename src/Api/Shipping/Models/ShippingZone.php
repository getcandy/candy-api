<?php
namespace GetCandy\Api\Shipping\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Countries\Models\Country;

class ShippingZone extends BaseModel
{
    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = [
        'name'
    ];

    public function methods()
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_zones');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'shipping_zone_country');
    }
}
