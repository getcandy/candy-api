<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Traits\HasCompletion;
use GetCandy\Api\Core\Discounts\Models\Discount;

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

    public function refresh()
    {
        return app('api')->baskets()->setTotals($this);
    }

    public function getWeightAttribute()
    {
        $weight = 0;
        foreach ($this->lines as $line) {
            $weight += (float) $line->variant->weight_value;
        }

        return $weight;
    }
}
