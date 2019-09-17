<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Traits\HasMeta;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Traits\HasCompletion;
use GetCandy\Api\Core\Discounts\Models\Discount;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Basket extends BaseModel
{
    use HasCompletion, HasMeta;

    protected $hashids = 'basket';

    /**
     * The basket sub total.
     *
     * @var int
     */
    public $sub_total = 0;

    /**
     * The basket total tax.
     *
     * @var int
     */
    public $total_tax = 0;

    /**
     * The basket total cost.
     *
     * @var int
     */
    public $total_cost = 0;

    /**
     * The basket discount total.
     *
     * @var int
     */
    public $discount_total = 0;

    /**
     * If the basket has been changed.
     *
     * @var bool
     */
    public $changed = false;

    /**
     * Whether the basket has exclusions
     *
     * @var boolean
     */
    public $hasExclusions = false;

    /**
     * The fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'lines', 'completed_at', 'merged_id', 'meta',
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

    public function discount($coupon)
    {
        return $this->discounts()->wherePivot('coupon', '=', $coupon)->first();
    }

    public function getExVatAttribute()
    {
        return $this->total - $this->tax;
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
        return $this->hasOne(Order::class, 'basket_id')->withoutGlobalScopes();
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'basket_id');
    }

    public function placedOrder()
    {
        return $this->hasOne(Order::class)->withoutGlobalScope('open')->where('placed_at', '!=', null);
    }

    /**
     * Get the saved basket relation.
     *
     * @return HasOne
     */
    public function savedBasket()
    {
        return $this->hasOne(SavedBasket::class);
    }

    /**
     * Determine whether this basket is saved.
     *
     * @return bool
     */
    public function isSaved()
    {
        return $this->savedBasket()->exists();
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
