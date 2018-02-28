<?php

namespace GetCandy\Api\Products\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Customers\Models\CustomerGroup;
use GetCandy\Api\Taxes\Models\Tax;

class ProductCustomerPrice extends BaseModel
{
    protected $fillable = [
        'customer_group_id',
        'tax_id',
        'price',
        'compare_at_price'
    ];

    /**
     * The Hashid Channel for encoding the id
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
