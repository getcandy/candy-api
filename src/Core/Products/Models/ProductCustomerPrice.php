<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Scopes\ProductPricingScope;
use GetCandy\Api\Core\Taxes\Models\Tax;

class ProductCustomerPrice extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_group_id',
        'tax_id',
        'price',
        'compare_at_price',
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

    /**
     * The Hashid connection name for enconding the id.
     *
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

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
