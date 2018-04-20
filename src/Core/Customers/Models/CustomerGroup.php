<?php

namespace GetCandy\Api\Core\Customers\Models;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;

class CustomerGroup extends BaseModel
{
    /**
     * @var string
     */
    protected $hashids = 'main';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return mixed
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('visible', 'purchasable');
    }

    /**
     * @return mixed
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withPivot('visible', 'purchasable');
    }

    /**
     * @return mixed
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot('visible', 'purchasable');
    }

    public function shippingPrices()
    {
        return $this->belongsToMany(ShippingPrice::class, 'shipping_customer_group_price')->withPivot('visible');
    }
}
