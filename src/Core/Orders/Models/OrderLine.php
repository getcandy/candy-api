<?php

namespace GetCandy\Api\Core\Orders\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class OrderLine extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = [
        'quantity',
        'description',
        'variant',
        'line_amount',
        'sku',
        'tax',
        'tax_rate',
        'discount',
        'total',
        'shipping',
        'delivery_total',

        // New fields
        'is_shipping',
        'unit_qty',
        'is_manual',
        'line_total',
        'unit_price',
        'discount_total',
        'tax_total',
        'tax_rate',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
