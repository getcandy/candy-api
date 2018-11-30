<?php

namespace GetCandy\Api\Core\Channels\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;

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
        'default',
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
