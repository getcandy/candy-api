<?php
namespace GetCandy\Api\Shipping\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\HasCustomerGroups;
use GetCandy\Api\Currencies\Models\Currency;
use GetCandy\Api\Customers\Models\CustomerGroup;

class ShippingPrice extends BaseModel
{
    use HasCustomerGroups;

    /**
     * @var string
     */
    protected $hashids = 'main';

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
        'min_volume'
    ];

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'shipping_customer_group_price')->withPivot(['visible']);
    }
}
