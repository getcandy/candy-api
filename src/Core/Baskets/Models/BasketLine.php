<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Traits\HasMeta;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class BasketLine extends BaseModel
{
    use HasMeta;

    protected $hashids = 'basket';

    public $total_cost;
    public $total_tax;
    public $unit_cost;
    public $unit_tax;
    public $unit_qty;
    public $base_cost;
    public $discount_total;

    protected $fillable = ['quantity', 'product_variant_id', 'total', 'meta'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }
}
