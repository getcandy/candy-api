<?php

namespace GetCandy\Api\Core\Discounts\Validators;

use Carbon\Carbon;
use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Baskets\BasketCriteria;

class DiscountValidator
{
    protected $baskets;

    public function __construct(BasketCriteria $baskets)
    {
        $this->baskets = $baskets;
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        foreach ($value as $criteria) {
            $previous = [];
            foreach ($criteria as $item) {
                if (! count(array_diff($item, $previous))) {
                    return false;
                }
                $previous = $item;
            }
        }

        return true;
    }

    public function checkCoupon($attribute, $value, $parameters, $validator)
    {
        // Get the current users basket...
        $basket = $this->baskets
            ->includes(['discounts'])
            ->id($parameters[0])
            ->first();

        // First off, if the coupon doesn't exist, then no..
        if (! $coupon = app('api')->discounts()->getByCoupon($value)) {
            return false;
        }

        $discount = $coupon->set->discount;

        if (Carbon::parse($discount->start_at)->isFuture() ||
            $discount->end_at && Carbon::parse($discount->end_at)->isPast()) {
            return false;
        }

        if (! $discount->status) {
            return false;
        }

        $factory = app('api')->discounts()->getFactory($discount);

        $check = (new Factory)->checkCriteria(
            $factory,
            $basket->user,
            $basket
        );

        if (! $check) {
            return false;
        }

        return ! $basket->discounts->filter(function ($discount) use ($value) {
            $incoming = strtoupper($value);
            $current = strtoupper($discount->pivot->coupon);
            if ($discount->stop_rules || ($incoming === $current)) {
                return true;
            }
        })->count();
    }
}
