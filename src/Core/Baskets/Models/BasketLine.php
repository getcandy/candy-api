<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class BasketLine extends BaseModel
{
    protected $hashids = 'basket';

    protected $fillable = ['quantity', 'product_variant_id', 'total'];

    public function getCurrentTotalAttribute()
    {
        $tieredPrice = app('api')->productVariants()->getTieredPrice($this->variant, $this->quantity, \Auth::user());

        $price = $this->variant->total_price;

        if ($tieredPrice) {
            $price = $tieredPrice->amount;
        }

        return $this->quantity * ($price / $this->variant->unit_qty);
    }

    public function getCurrentTaxAttribute()
    {
        $tieredPrice = app('api')->productVariants()->getTieredPrice($this->variant, $this->quantity, \Auth::user());

        $tax = $this->quantity * $this->variant->tax_total;

        if ($tieredPrice) {
            $tax = $this->quantity * $tieredPrice->tax;
        }

        return $tax / $this->variant->unit_qty;
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
