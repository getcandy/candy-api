<?php

namespace GetCandy\Api\Core\Products\Models;

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
