<?php

namespace GetCandy\Api\Core\Orders\Models;

use GetCandy\Api\Core\Traits\HasMeta;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class OrderLine extends BaseModel
{
    use HasMeta;

    protected $hashids = 'order';

    protected $fillable = [
        'quantity',
        'description',
        'option',
        'meta',
        'line_amount',
        'sku',
        'tax',
        'tax_rate',
        'discount',
        'total',
        'shipping',
        'delivery_total',
        'product_variant_id',

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
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
