<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasCustomerGroups;

class ShippingPrice extends BaseModel
{
    use HasCustomerGroups;

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
        'rate',
        'fixed',
        'min_basket',
        'min_weight',
        'weight_unit',
        'min_width',
        'width_unit',
        'min_height',
        'height_unit',
        'min_depth',
        'depth_unit',
        'volume_unit',
        'min_volume',
    ];

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'shipping_customer_group_price')->withPivot(['visible']);
    }
}
