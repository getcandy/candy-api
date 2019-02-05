<?php

namespace GetCandy\Api\Core\Discounts\Criteria;

use GetCandy\Api\Core\Discounts\Contracts\DiscountCriteriaContract;

class Coupon implements DiscountCriteriaContract
{
    public function getArea()
    {
        return 'basket';
    }

    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLabel()
    {
        return 'Coupon code';
    }

    public function getHandle()
    {
        return 'coupon';
    }

    public function check($user = null, $product = null, $basket = null)
    {
        if (! $basket) {
            return false;
        }

        $discounts = $basket->discounts ?: collect();

        $coupons = $discounts->map(function ($item) {
            return strtoupper($item->pivot->coupon);
        });

        return ! $coupons->contains(
            strtoupper($this->value)
        );
    }
}
