<?php

namespace GetCandy\Api\Core\Discounts\Validators;

use Carbon\Carbon;
use GetCandy\Api\Core\Discounts\Factory;

class DiscountValidator
{
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
        $basket = app('api')->baskets()->getByHashedId($parameters[0]);

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
            if ($discount->stop_rules || ($discount->pivot->coupon === $value)) {
                return true;
            }
        })->count();
    }
}
