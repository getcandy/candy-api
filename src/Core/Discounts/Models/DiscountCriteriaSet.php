<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class DiscountCriteriaSet extends BaseModel
{
    protected $fillable = ['scope', 'outcome'];

    protected $hashids = 'main';

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function items()
    {
        return $this->hasMany(DiscountCriteriaItem::class);
    }

    /**
     * Process a criteria set.
     *
     * @param \Illuminate\Eloquent\Database\Model $user
     * @param \GetCandy\Core\Baskets\Models\Basket $basket
     * @return bool
     */
    public function process($user, $basket)
    {
        $apply = false;
        foreach ($this->items as $item) {
            $apply = $item->check($user, $basket);
        }

        return $apply;
    }
}
