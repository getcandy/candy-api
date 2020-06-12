<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class DiscountCriteriaSet extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['scope', 'outcome'];

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $basket
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
