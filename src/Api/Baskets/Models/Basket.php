<?php

namespace GetCandy\Api\Baskets\Models;

use PriceCalculator;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Orders\Models\Order;
use GetCandy\Api\Traits\HasCompletion;
use GetCandy\Api\Discounts\Models\Discount;

class Basket extends BaseModel
{
    use HasCompletion;

    protected $hashids = 'basket';

    protected $fillable = [
        'lines', 'completed_at', 'merged_id',
    ];

    /**
     * Get the basket lines.
     *
     * @return void
     */
    public function lines()
    {
        return $this->hasMany(BasketLine::class);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class)->withPivot('coupon');
    }

    public function getExVatAttribute()
    {
        return round($this->total - $this->tax, 2);
    }

    /**
     * Get the basket user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function order()
    {
        return $this->hasOne(Order::class)->withoutGlobalScopes();
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class)->withoutGlobalScope('open');
    }

    // protected function getTotalWithoutDiscountAttribute()
    // {
    //     // $subTotal = 0;
    //     // foreach ($this->lines as $line) {
    //     //     if ($line->variant->tax) {
    //     //         $tieredPrice = app('api')->productVariants()->getTieredPrice($line->variant, $line->quantity, \Auth::user());
    //     //         if ($tieredPrice) {
    //     //             $taxTotal += $tieredPrice->tax;
    //     //         } else {
    //     //             $taxTotal += PriceCalculator::get($line->current_total, $line->variant->tax)->amount;
    //     //         }
    //     //     }
    //     // }
    //     // return $total;
    // }

    // public function getTaxTotalAttribute()
    // {

    //     dd($taxTotal);
    //     return $taxTotal;
    // }

    public function getWeightAttribute()
    {
        $weight = 0;
        foreach ($this->lines as $line) {
            $weight += (float) $line->variant->weight_value;
        }

        return $weight;
    }
}
