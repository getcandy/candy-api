<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Scopes\ProductPricingScope;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

class ProductPricingTier extends BaseModel
{
    protected $fillable = [
        'customer_group_id',
        'product_variant_id',
        'lower_limit',
        'price',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProductPricingScope);
    }

    public function scopeInGroups($query, $groups)
    {
        return $query->whereHas('group', function ($q) use ($groups) {
            $q->whereIn('id', $groups);
        });
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getTotalCostAttribute()
    {
        return app()->getInstance()->make(PriceCalculatorInterface::class)->get($this->price, 'default')->total_cost;
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getTotalTaxAttribute()
    {
        return app()->getInstance()->make(PriceCalculatorInterface::class)->get($this->price, 'default')->total_tax;
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getLimitTaxAttribute()
    {
        return app()->getInstance()->make(PriceCalculatorInterface::class)->get($this->price * $this->lower_limit, 'default')->total_tax;
    }

    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product_family';

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    protected function getCustomerGroups()
    {
        // If there is a user, get their groups.
        if ($user = app('auth')->user()) {
            return $user->groups->pluck('id')->toArray();
        } else {
            return [app('api')->customerGroups()->getGuestId()];
        }
    }
}
