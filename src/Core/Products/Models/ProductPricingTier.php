<?php

namespace GetCandy\Api\Core\Products\Models;

use PriceCalculator;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class ProductPricingTier extends BaseModel
{
    protected $fillable = [
        'customer_group_id',
        'product_variant_id',
        'lower_limit',
        'price',
    ];

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
        return PriceCalculator::get($this->price)->total_cost;
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getTotalTaxAttribute()
    {
        return PriceCalculator::get($this->price)->total_tax;
    }

    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product_family';

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
