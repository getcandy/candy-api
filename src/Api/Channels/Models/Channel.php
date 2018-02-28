<?php

namespace GetCandy\Api\Channels\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Discounts\Models\Discount;
use GetCandy\Api\Categories\Models\Category;
use GetCandy\Api\Collections\Models\Collection;
use GetCandy\Api\Shipping\Models\ShippingMethod;

class Channel extends BaseModel
{
    protected $hashids = 'channel';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handle',
        'default'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('published_at');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot('published_at');
    }
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withPivot('published_at');
    }
    public function discounts()
    {
        return $this->belongsToMany(Discount::class)->withPivot('published_at');
    }

    public function shippingMethods()
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_channel')->withPivot('published_at');
    }

    public function discount()
    {
        return $this->hasMany(Discount::class);
    }
}
