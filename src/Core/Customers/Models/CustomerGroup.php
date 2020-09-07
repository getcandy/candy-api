<?php

namespace GetCandy\Api\Core\Customers\Models;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;

class CustomerGroup extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = ['name', 'handle', 'default'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('visible', 'purchasable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withPivot('visible', 'purchasable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
